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
    protected function configure(): void
    {
        $this
            ->addOption('dirty', null, InputArgument::OPTIONAL)
            ->addOption('dir', null, InputArgument::OPTIONAL)
            ->addOption('ignore', null, InputArgument::OPTIONAL)
            ->addOption('text', null, InputArgument::OPTIONAL)
            ->addOption('ignore-files', null, InputArgument::OPTIONAL)
            ->addArgument('stop-on-failure', InputArgument::OPTIONAL);
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dirtyFiles = [];

        $output->writeln('');

        if (empty($input->getOption('dir'))) {
            $output->writeln('  👋️  <error>Whoops. Specify the folders you need to search in DS_CHECK_IN_DIR in the comma separated .env file</error>');
            $output->writeln('');

            return Command::FAILURE;
        }

        $output->writeln('    <info>LaraDumps is searching for words used in debugging in: ' . $input->getOption('dir') . '</info>');

        if (!empty($input->getOption('dirty'))) {
            $dirtyFiles = GitDirtyFiles::run();

            if (empty($dirtyFiles)) {
                render(<<<HTML
<div class="mx-1">
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
HTML);

                return Command::SUCCESS;
            }
        }

        $ignoreLineWhenContainsText = $this->prepareTextToIgnore($input);

        $textToSearch = $this->prepareTextToSearch($input);

        renderUsing($output);

        $matches = [];

        $finder = new Finder();

        $finder->files()
            ->ignoreVCSIgnored(true)
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

                /** @var string[] $ignoreLineWhenContainsText */
                foreach ($ignoreLineWhenContainsText as $text) {
                    if (strpos(strtolower($lineContent), strtolower($text))) {
                        $ignore = true;

                        break;
                    }
                }

                /** @var string[] $textToSearch */
                foreach ($textToSearch as $search) {
                    $search = ' ' . ltrim($search); // maintaining compatibility with V1.0.2;

                    if (strpos($lineContent, $search)) {
                        $contains = true;

                        break;
                    }
                }

                if ($contains && !$ignore) {
                    $matches[] = $this->saveContent($file, $lineContent, $line);

                    if ($input->getArgument('stop-on-failure')) {
                        break 2;
                    }
                }
            }
        }

        $output->writeln('');

        foreach ($matches as $iterator => $content) {
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

        $progressBar->finish();

        $output->writeln('');

        if (($total = count($matches)) > 0) {
            $totalFiles = count(array_unique(array_column($matches, 'realPath')));

            $errorMessage = ($total === 1) ? 'error' : 'errors';
            $fileMessage  = ($totalFiles === 1) ? 'file' : 'files';

            $message = '[ERROR] Found ' . $total . ' ' . $errorMessage . ' / ' . $totalFiles . ' ' . $fileMessage;

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

            return Command::FAILURE;
        }

        $output->writeln('');

        render(<<<HTML
            <div class="mx-1">
                No ds() found.
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
        HTML);

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

        $default = 'ds,dsq,dsd,ds1,ds2,ds3,ds4,ds5';

        $checkInFor = $input->getOption('text') ?? "";

        $values = explode(",", $checkInFor);

        $mergedValues = array_unique(array_merge(explode(",", $default), $values));

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

        foreach (explode(",", $input->getOption('ignore') ?? "") as $search) {
            if (!empty($search)) {
                $array[] = $search;
            }
        }

        return $array;
    }

    private function saveContent(\SplFileInfo $file, string $lineContent, int $line): array
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
}
