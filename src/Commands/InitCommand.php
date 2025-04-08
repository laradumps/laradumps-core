<?php

namespace LaraDumps\LaraDumpsCore\Commands;

use LaraDumps\LaraDumpsCore\Actions\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'init',
    description: 'Init',
    hidden: false
)]
class InitCommand extends Command
{
    protected function configure()
    {
        $this->addArgument('pwd', InputArgument::OPTIONAL, 'The working directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $pwd */
        $pwd = $input->getArgument('pwd');

        if (is_null($pwd)) {
            $pwd = appBasePath();
        } else {
            $pwd = rtrim($pwd, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        $publish = Config::publish(
            pwd: $pwd,
            filepath: __DIR__ . '/laradumps-base.yaml'
        );

        if (!$publish) {
            $output->writeln('');
            $output->writeln('  ❌  <error>Failed to publish the configuration file.</error>');
            $output->writeln('');

            return Command::FAILURE;
        }

        $output->writeln('');
        $output->writeln('  ✅  <info>LaraDumps has been successfully configured!</info>');
        $output->writeln('');

        ds('Welcome back to the LaraDumps!');

        new Process(
            [
                'echo',
                '"laradumps.yaml"',
                '>>',
                '.gitignore',
            ],
            $pwd
        );

        return Command::SUCCESS;
    }
}
