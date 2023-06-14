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
        $laradumps = ds()->configure();

        if (!$laradumps->getDispatch()) {
            $output->writeln('');
            $output->writeln(' ⚠️ <comment> Is the LaraDumps Application open? </comment>');
            $output->writeln(' Download: <href=https://laradumps.dev/get-started/installation.html#desktop-app>https://laradumps.dev/get-started/installation.html#desktop-app</>');
            $output->writeln('');
        }

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
