<?php

namespace LaraDumps\LaraDumpsCore\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'init',
    description: 'Init',
    hidden: false
)]
class InitCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileContent = Yaml::parseFile(__DIR__ . '/Commands/laradumps-sample.yaml');
        $yamlContent = Yaml::dump($fileContent);

        $filePath = appBasePath() . 'laradumps.yaml';

        file_put_contents($filePath, $yamlContent);

        return Command::SUCCESS;
    }
}
