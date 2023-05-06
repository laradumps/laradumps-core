<?php

namespace LaraDumps\LaraDumpsCore\Commands;

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

        return Command::SUCCESS;
    }
}
