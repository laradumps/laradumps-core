<?php

use LaraDumps\LaraDumpsCore\Commands\CheckCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

function startCommandApplication(array $arguments): CommandTester
{
    $application = new Application();
    $application->add(new CheckCommand());

    $command = $application->find('check');

    $commandTester = new CommandTester($command);

    $commandTester->execute([
        'command' => $command->getName(),
        ...$arguments,
    ]);

    return $commandTester;
}

it('show message if variable ... is empty', function () {
    $commandTester = startCommandApplication([]);

    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('Whoops. Specify the folders you need to search in --dir option in the comma separated');
});

it('check command work property', function () {
    $commandTester = startCommandApplication([
        '--dir'          => sprintf('tests%sFixtures', DIRECTORY_SEPARATOR),
        '--ignore-files' => vsprintf('tests%sFixtures%sds_env, tests%sFixtures%sAnotherFunctionsToCheck.php', [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR]),
    ]);

    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('LaraDumps is searching for words used in debugging in: ' . sprintf('tests%sFixtures', DIRECTORY_SEPARATOR))
        ->and($output)
        ->not->toContain('Whoops. Specify the folders you need to search in --dir option in the comma separated')
        ->toContain('1/2')
        ->toContain(
            'ds(\'this is a function to check!\')',
            '@ds("this is a function to check!")',
        )
        ->not->toContain(
            'dump(\'this is a function to check!\')',
            'dd(\'this is a function to check!\')',
            '//ds(\'this is a function to check!\')'
        )
        ->toContain('ERROR - Found 2 errors / 1 file');
});

it('check command with "dump", "dd" work property', function () {
    $commandTester = startCommandApplication([
        '--dir'          => sprintf('tests%sFixtures', DIRECTORY_SEPARATOR),
        '--ignore-files' => vsprintf('tests%sFixtures%sds_env, tests%sFixtures%sExampleClassToCheck.php', [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR]),
        '--text'         => 'dump,dd',
    ]);

    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('LaraDumps is searching for words used in debugging in: ' . sprintf('tests%sFixtures', DIRECTORY_SEPARATOR))
        ->and($output)
        ->not->toContain('Whoops. Specify the folders you need to search in --dir option in the comma separated')
        ->toContain('1/2')
        ->toContain(
            'dump(\'this is a function to check!\')',
            'dd(\'this is a function to check!\')',
            '//dd(\'this is a function to check!\')'
        )
        ->toContain('ERROR - Found 3 errors / 1 file');
});

it('check command without "dump", "dd" work property', function () {
    $commandTester = startCommandApplication([
        '--dir'          => sprintf('tests%sFixtures', DIRECTORY_SEPARATOR),
        '--ignore-files' => vsprintf('tests%sFixtures%sds_env, tests%sFixtures%sAnotherFunctionsToCheck.php', [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR]),
    ]);

    $output = $commandTester->getDisplay();

    expect($output)
        ->toContain('LaraDumps is searching for words used in debugging in: ' . sprintf('tests%sFixtures', DIRECTORY_SEPARATOR))
        ->and($output)
        ->not->toContain('Whoops. Specify the folders you need to search in --dir option in the comma separated')
        ->toContain('1/2')
        ->toContain(
            'ds(\'this is a function to check!\');',
            ' @ds("this is a function to check!")',
        )
        ->toContain('ERROR - Found 2 errors / 1 file');
});
