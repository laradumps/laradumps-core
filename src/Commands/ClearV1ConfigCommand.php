<?php

namespace LaraDumps\LaraDumpsCore\Commands;

use Dotenv\Dotenv;
use Exception;
use LaraDumps\LaraDumpsCore\Actions\WriteEnv;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'clear-v1-config',
    description: 'Comment LaraDumps v1 config',
    hidden: false
)]
class ClearV1ConfigCommand extends Command
{
    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = appBasePath() . 'config' . DIRECTORY_SEPARATOR . 'laradumps.php';

        if (file_exists($configPath)) {
            (new Process(['rm', '-r', '-f', $configPath]))->run();
        }

        $dotenv = Dotenv::createImmutable(appBasePath(), '.env');
        $dotenv->load();

        $keysToComment = [];

        foreach ($_ENV as $key => $value) {
            if (str_contains($key, 'DS_')) {
                $keysToComment[] = $key;
            }
        }

        WriteEnv::commentOldEnvKeys(appBasePath() . '.env', $keysToComment);

        $output->writeln('');
        $output->writeLn('<info>√ config/laradumps.php has been successfully deleted</info>');
        $output->writeLn('<info>√ LaraDumps v1 environment has been successfully commented</info>');

        return Command::SUCCESS;
    }
}
