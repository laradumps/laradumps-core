<?php

namespace LaraDumps\LaraDumpsCore\Commands;

use LaraDumps\LaraDumpsCore\Actions\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'configure',
    description: 'Configure Desktop App',
    hidden: false
)]
class ConfigureDesktopAppCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ds()->configure();

        if (Config::get('host') === 'host.docker.internal') {
            $output->writeln('');
            $output->writeln(' 🐳 <comment> It looks like you are using docker. </comment>');
            $output->writeln('');
            $output->writeln(' 💡 In your terminal in root (outside the container). In the root of your project run: pwd');
            $output->writeln(' 💡 In app: "<info>PHP Project Path: (when docker)</info>" paste pwd value');
            $output->writeln('');
            $output->writeln('  <info>  Finish configuring in the app</info>');
        }

        return Command::SUCCESS;
    }
}
