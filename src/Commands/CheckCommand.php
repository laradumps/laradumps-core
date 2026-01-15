<?php

namespace LaraDumps\LaraDumpsCore\Commands;

use Exception;
use LaraDumps\LaraDumpsCore\Actions\GitDirtyFiles;
use LaraDumps\LaraDumpsCore\Support\CheckCodeSnippet;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;

use function Termwind\{render, renderUsing};

#[AsCommand(
    name: 'check',
    description: 'Check if you forgot any ds() in your files',
    hidden: false
)]
class CheckCommand extends Command
{
    private string $defaultTextToSearch = 'ds[qd12345]?';

    private string $defaultTextToIgnore = '@dsAutoClearOnPageReload';

    protected function configure(): void
    {
        $this
            ->addOption('dirty', null, InputArgument::OPTIONAL, 'Search only files that are dirty in git')
            ->addOption('dir', null, InputArgument::OPTIONAL, 'Directories that will be filtered separated by comma')
            ->addOption('ignore', null, InputArgument::OPTIONAL, 'Directories to be ignored separated by comma')
            ->addOption('text', null, InputArgument::OPTIONAL, 'Texts that will be searched separated by a comma')
            ->addOption('extension', null, InputArgument::OPTIONAL, 'File extensions that will be searched separated by a comma')
            ->addOption('ignore-files', null, InputArgument::OPTIONAL, 'Files that will be ignored separated by a comma')
            ->addArgument('stop-on-failure', InputArgument::OPTIONAL, 'Stop the search if a match is found')
            ->addOption('exactly', null, InputArgument::OPTIONAL, 'Search for exact occurrences');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);

        if (function_exists('Termwind\renderUsing')) {
            renderUsing($output);
        }

        $output->writeln('');

        if (empty($input->getOption('dir'))) {
            $output->writeln(' 👋️ <error>Whoops. Specify the folders you need to search in --dir option in the comma separated</error>');
            $output->writeln('');

            return Command::FAILURE;
        }

        if ($input->getOption('exactly')) {
            if (empty($input->getOption('text'))) {
                $output->writeln(' 👋️ <error>Whoops. Specify the --text option when using the --exactly option</error>');
                $output->writeln('');

                return Command::FAILURE;
            }

            $this->defaultTextToSearch = '';
        }

        $output->writeln(' 🔍 <info>LaraDumps is searching for words used in debugging in: ' . $input->getOption('dir') . '</info>');

        $dirtyFiles = [];

        if (!empty($input->getOption('dirty'))) {
            $dirtyFiles = GitDirtyFiles::run();

            if (empty($dirtyFiles)) {
                $this->displaySuccess($output, '0');

                return Command::SUCCESS;
            }
        }

        $extensions = ['php'];

        if (!empty($input->getOption('extension'))) {
            $extensions = explode(',', $input->getOption('extension'));
        }

        $matches = [];

        $finder = (new Finder())->files()
            ->ignoreVCS(true)
            ->exclude('node_modules')
            ->in($this->prepareDirectories($input));

        foreach ($extensions as $extension) {
            $finder->name("*.$extension");
        }

        $progressBar = new ProgressBar($output, count($dirtyFiles) ?: $finder->count());

        $output->writeln('');

        $filesToIgnore = $this->prepareFilesToIgnore($input);
        $textToIgnore  = $this->prepareTextToIgnore($input);

        foreach ($finder as $file) {
            if ($dirtyFiles && !in_array($file->getRealPath(), $dirtyFiles)) {
                continue;
            }

            if (in_array($file->getRealPath(), $filesToIgnore)) {
                continue;
            }

            $progressBar->advance();

            /** @var string[] $contents */
            $contents = file($file->getRealPath());

            foreach ($contents as $line => $lineContent) {
                $contains = false;
                $ignore   = false;

                foreach ($textToIgnore as $text) {
                    if (strpos(strtolower($lineContent), strtolower($text))) {
                        $ignore = true;

                        break;
                    }
                }

                foreach ($this->prepareTextToSearch($input) as $search) {
                    $search = ltrim($search);

                    if (preg_match("/$search/", $lineContent)) {
                        $contains = true;

                        break;
                    }
                }

                if ($contains && !$ignore) {
                    $matches[] = $this->addMatchToDisplay($file, $line);

                    if ($input->getArgument('stop-on-failure')) {
                        break 2;
                    }
                }
            }
        }

        $output->writeln('');

        foreach ($matches as $iterator => $content) {
            $this->displayCodeBlock($output, $iterator, $content);
        }

        $progressBar->finish();

        $duration = $this->getDuration($startTime);

        if (($total = count($matches)) > 0) {
            $this->displayErrorFound($output, $total, $matches, $duration);

            return Command::FAILURE;
        }

        $this->displaySuccess($output, $duration);

        return Command::SUCCESS;
    }

    private function getDuration(float $startTime): string
    {
        $duration = ((microtime(true) - $startTime) * 1000);

        if ($duration > 60000) {
            $mins     = floor($duration / 60000);
            $secs     = round((fmod($duration, 60000) / 1000), 2);
            $duration = $mins . ' mins';

            if ($secs !== 0) {
                $duration .= ", $secs secs";
            }

            return $duration;
        }

        if ($duration > 1000) {
            return round(($duration / 1000), 2) . ' secs';
        }

        return round($duration) . 'ms';
    }

    private function prepareDirectories(InputInterface $input): array
    {
        $array = [];

        foreach (explode(',', $input->getOption('dir') ?? '') as $dir) {
            if (!empty($dir)) {
                $array[] = appBasePath() . trim($dir);
            }
        }

        return $array;
    }

    private function prepareFilesToIgnore(InputInterface $input): array
    {
        $array = [];

        foreach (explode(',', $input->getOption('ignore-files') ?? '') as $dir) {
            if (!empty($dir)) {
                $array[] = appBasePath() . trim($dir);
            }
        }

        return $array;
    }

    private function prepareTextToSearch(InputInterface $input): array
    {
        $textToSearch = [];

        $checkInFor = $input->getOption('text') ?? '';

        $values = explode(',', $checkInFor);

        $mergedValues = array_unique(
            array_merge(
                explode(
                    ',',
                    $this->defaultTextToSearch
                ),
                $values
            )
        );

        foreach ($mergedValues as $search) {
            $search = trim($search);

            if (strlen($search) > 0) {
                $textToSearch[] = '(^|\W)' . $search . '\(';
            }
        }

        return $textToSearch;
    }

    private function prepareTextToIgnore(InputInterface $input): array
    {
        $array = [];

        $ignore = $input->getOption('ignore') ?? '';

        $values = explode(',', $ignore);

        $mergedValues = array_unique(array_merge(explode(',', $this->defaultTextToIgnore), $values));

        foreach ($mergedValues as $search) {
            if (!empty($search)) {
                $array[] = $search;
            }
        }

        return $array;
    }

    private function addMatchToDisplay(\SplFileInfo $file, int $line): CheckCodeSnippet
    {
        return new CheckCodeSnippet($file, $line);
    }

    private function displayCodeBlock(OutputInterface $output, int $iterator, CheckCodeSnippet $snippet): void
    {
        $output->writeln('');

        $output->writeln(
            ' ' . ($iterator + 1)
            . ' <href=' . $snippet->realPath . '>'
            . $snippet->realPath
            . ':'
            . $snippet->line
            . '</>'
        );

        if (function_exists('Termwind\render')) {
            render(<<<HTML
            <div class="space-x-1 mx-2 mb-1">
                <code line="{$snippet->line}" start-line="{$snippet->startLine}">
                {$snippet->contents}
                </code>
            </div>
            HTML);

            return;
        }

        $output->writeln($snippet->contents);
    }

    private function displaySuccess(OutputInterface $output, string $duration): void
    {
        if (function_exists('Termwind\render')) {
            render(
                <<<HTML
<div>
    <div class="flex">
        <span class="flex-1 content-repeat-[-] text-gray"></span>
    </div>
    <div>
        <div class="text-green ml-2">
          ✅  <span class="mx-1"><span class="font-bold">SUCCESS</span> - No results found</span>
        </div>
        <div class=" ml-2 mt-0.5">
           🕗 Duration: $duration
        </div>
    </div>
    <div></div>
</div>
HTML
            );

            return;
        }

        $output->writeln('  ✅  <info> SUCCESS </info> - No results found');
        $output->writeln("  🕗 Duration: $duration");
    }

    private function displayErrorFound(OutputInterface $output, int $total, array $matches, string $duration): void
    {
        $totalFiles = count(array_unique(array_column($matches, 'realPath')));

        $totalErrorMessage = ($total === 1) ? 'error' : 'errors';
        $totalFileMessage  = ($totalFiles === 1) ? 'file' : 'files';

        $message = 'Found ' . $total . ' ' . $totalErrorMessage . ' / ' . $totalFiles . ' ' . $totalFileMessage;

        if (function_exists('Termwind\render')) {
            render(
                <<<HTML
<div>
    <div class="flex">
        <span class="flex-1 content-repeat-[-] text-gray"></span>
    </div>
    <div>
        <div class="text-red ml-2">
          ❌  <span class="mx-1"><span class="font-bold">ERROR</span> - $message</span>
        </div>
        <div class=" ml-2 mt-0.5">
           🕗 Duration: $duration
        </div>
    </div>
    <div></div>
</div>
HTML
            );

            return;
        }

        $output->writeln("  ❌  <error> ERROR </error> - $message");
        $output->writeln("  🕗 Duration: $duration");
    }
}
