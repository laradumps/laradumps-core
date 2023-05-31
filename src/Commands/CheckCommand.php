<?php

namespace LaraDumps\LaraDumpsCore\Commands;

use Exception;
use LaraDumps\LaraDumpsCore\Actions\{GitDirtyFiles, MakeFileHandler};
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
    private string $defaultTextToSearch = 'ds,dsq,dsd,ds1,ds2,ds3,ds4,ds5';

    private string $defaultTextToIgnore = '@dsAutoClearOnPageReload';

    protected function configure(): void
    {
        $this
            ->addOption('dirty', null, InputArgument::OPTIONAL, 'Search only files that are dirty in git')
            ->addOption('dir', null, InputArgument::OPTIONAL, 'Directories that will be filtered separated by comma')
            ->addOption('ignore', null, InputArgument::OPTIONAL, 'Directories to be ignored separated by comma')
            ->addOption('text', null, InputArgument::OPTIONAL, 'Texts that will be searched separated by a comma')
            ->addOption('ignore-files', null, InputArgument::OPTIONAL, 'Files that will be ignored separated by a comma')
            ->addArgument('stop-on-failure', InputArgument::OPTIONAL, 'Stop the search if a match is found');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);

        renderUsing($output);

        $output->writeln('');

        if (empty($input->getOption('dir'))) {
            $output->writeln('  👋️  <error>Whoops. Specify the folders you need to search in --dir option in the comma separated</error>');
            $output->writeln('');

            return Command::FAILURE;
        }

        $output->writeln('    <info>LaraDumps is searching for words used in debugging in: ' . $input->getOption('dir') . '</info>');

        $dirtyFiles = [];

        if (!empty($input->getOption('dirty'))) {
            $dirtyFiles = GitDirtyFiles::run();

            if (empty($dirtyFiles)) {
                $this->displaySuccess('0');

                return Command::SUCCESS;
            }
        }

        $matches = [];

        $finder = (new Finder())->files()
            ->ignoreVCS(true)
            ->exclude('node_modules')
            ->name('*.php')
            ->in($this->prepareDirectories($input));

        $progressBar = new ProgressBar($output, count($dirtyFiles) ?: $finder->count());

        $output->writeln('');

        foreach ($finder as $file) {
            if ($dirtyFiles && !in_array($file->getRealPath(), $dirtyFiles)) {
                continue;
            }

            if (in_array($file->getRealPath(), $this->prepareFilesToIgnore($input))) {
                continue;
            }

            $progressBar->advance();

            /** @var string[] $contents */
            $contents = file($file->getRealPath());

            foreach ($contents as $line => $lineContent) {
                $contains = false;
                $ignore   = false;

                foreach ($this->prepareTextToIgnore($input) as $text) {
                    if (strpos(strtolower($lineContent), strtolower($text))) {
                        $ignore = true;

                        break;
                    }
                }

                foreach ($this->prepareTextToSearch($input) as $search) {
                    $search = ' ' . ltrim($search);

                    if (strpos($lineContent, $search)) {
                        echo 'search: ' . $search;
                        $contains = true;

                        break;
                    }
                }

                if ($contains && !$ignore) {
                    $matches[] = $this->addMatchToDisplay($file, $lineContent, $line);

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

        $duration = number_format((microtime(true) - $startTime) * 1000, 2);

        $output->writeln('');

        if (($total = count($matches)) > 0) {
            $this->displayErrorFound($total, $matches);

            return Command::FAILURE;
        }

        $output->writeln('');

        $this->displaySuccess($duration);

        return Command::SUCCESS;
    }

    private function prepareDirectories(InputInterface $input): array
    {
        $array = [];

        foreach (explode(",", $input->getOption('dir') ?? "") as $dir) {
            if (!empty($dir)) {
                $array[] = appBasePath() . trim($dir);
            }
        }

        return $array;
    }

    private function prepareFilesToIgnore(InputInterface $input): array
    {
        $array = [];

        foreach (explode(",", $input->getOption('ignore-files') ?? "") as $dir) {
            if (!empty($dir)) {
                $array[] = appBasePath() . trim($dir);
            }
        }

        return $array;
    }

    private function prepareTextToSearch(InputInterface $input): array
    {
        $textToSearch = [];

        $checkInFor = $input->getOption('text') ?? "";

        $values = explode(",", $checkInFor);

        $mergedValues = array_unique(array_merge(explode(",", $this->defaultTextToSearch), $values));

        foreach ($mergedValues as $search) {
            $search = trim($search);

            if (strlen($search) > 0) {
                $textToSearch[] = ' ' . $search;
                $textToSearch[] = $search;
                $textToSearch[] = '//' . $search;
                $textToSearch[] = '->' . $search;
                $textToSearch[] = $search . '(';
                $textToSearch[] = '@' . $search;
                $textToSearch[] = ' @' . $search;
            }
        }

        return $textToSearch;
    }

    private function prepareTextToIgnore(InputInterface $input): array
    {
        $array = [];

        $ignore = $input->getOption('ignore') ?? "";

        $values = explode(",", $ignore);

        $mergedValues = array_unique(array_merge(explode(",", $this->defaultTextToIgnore), $values));

        foreach ($mergedValues as $search) {
            if (!empty($search)) {
                $array[] = $search;
            }
        }

        return $array;
    }

    private function addMatchToDisplay(\SplFileInfo $file, string $lineContent, int $line): array
    {
        /** @var array $fileContents */
        $fileContents = file($file->getRealPath());

        $partialContent = $fileContents[$line - 2] ?? '';
        $partialContent .= $fileContents[$line - 1] ?? '';

        $partialContent .= $lineContent;
        $partialContent .= $fileContents[$line + 1] ?? '';

        return [
            'line'     => $line + 1,
            'file'     => str_replace(appBasePath() . '/', '', $file->getRealPath()),
            'realPath' => 'file:///' . $file->getRealPath(),
            'link'     => MakeFileHandler::handle([
                'file' => $file->getRealPath(), 'line' => $line + 1,
            ]),
            'content' => $partialContent,
        ];
    }

    private function displaySuccess(string $duration): void
    {
        render(
            <<<HTML
<div class="mx-1">
    No ds() found in $duration ms
    <div class="flex space-x-1">
        <span class="flex-1 content-repeat-[─] text-gray"></span>
    </div>
    <div>
        <span>
            <div class="flex space-x-2 mx-1 mb-1">
                 <span class="px-2 bg-green text-white uppercase font-bold">
                      ✓ SUCCESS
                 </span>
            </div>
        </span>
    </div>
</div>
HTML
        );
    }

    private function displayCodeBlock(OutputInterface $output, int $iterator, array $content): void
    {
        $output->writeln(
            ' ' . ($iterator + 1)
            . '<href=' . $content['link'] . '>  '
            . $content['realPath']
            . ':'
            . $content['line']
            . '</>'
        );

        $line    = $content['line'] - 2;
        $content = $content['content'];

        render(<<<HTML
            <div class="space-x-1 mx-2 mb-1">
                <code line="$line" start-line="$line">
                    $content
                </code>
            </div>
            HTML);
    }

    private function displayErrorFound(int $total, array $matches): void
    {
        $totalFiles = count(array_unique(array_column($matches, 'realPath')));

        $totalErrorMessage = ($total === 1) ? 'error' : 'errors';
        $totalFileMessage  = ($totalFiles === 1) ? 'file' : 'files';

        $message = '[ERROR] Found ' . $total . ' ' . $totalErrorMessage . ' / ' . $totalFiles . ' ' . $totalFileMessage;

        render(
            <<<HTML
<div class="mx-1">
    <div class="flex space-x-1">
        <span class="flex-1 content-repeat-[─] text-gray"></span>
    </div>
    <div>
        <span>
            <div class="flex space-x-2 mx-1 mb-1">
                 <span class="p-2 bg-red text-white">
                 $message
                 </span>
            </div>
        </span>
    </div>
</div>
HTML
        );
    }
}
