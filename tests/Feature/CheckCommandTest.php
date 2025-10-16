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

function stripAnsiColors(string $text): string
{
    return preg_replace('/\e\[[0-9;]*[mK]/', '', $text);
}

it('shows message if "dir" parameter is empty', function () {
    $commandTester = startCommandApplication([]);

    $output = stripAnsiColors($commandTester->getDisplay());

    expect($output)
        ->toContain('Whoops. Specify the folders you need to search in --dir option in the comma separated');
});

it('checks command works properly', function () {
    $commandTester = startCommandApplication([
        '--dir'          => sprintf('tests%sFixtures', DIRECTORY_SEPARATOR),
        '--ignore-files' => vsprintf('tests%sFixtures%sds_env, tests%sFixtures%sAnotherFunctionsToCheck.php', [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR]),
    ]);

    $output = stripAnsiColors($commandTester->getDisplay());

    expect($output)
        ->toContain('LaraDumps is searching for words used in debugging in: ' . sprintf('tests%sFixtures', DIRECTORY_SEPARATOR))
        ->and($output)
        ->not->toContain('Whoops. Specify the folders you need to search in --dir option in the comma separated')
        ->toContain('1/3')
        ->toContain(
            'ds(\'this is a function to check!\')',
            '@ds("this is a function to check!")',
        )
        ->not->toContain(
            'dump(\'this is a function to check!\')',
            'dd(\'this is a function to check!\')',
            '//ds(\'this is a function to check!\')'
        )
        ->toContain('ERROR - Found 4 errors / 2 files');
});

it('checks command with "dump", "dd" works properly', function () {
    $commandTester = startCommandApplication([
        '--dir'          => sprintf('tests%sFixtures', DIRECTORY_SEPARATOR),
        '--ignore-files' => vsprintf('tests%sFixtures%sds_env, tests%sFixtures%sExampleClassToCheck.php', [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR]),
        '--text'         => 'dump,dd',
    ]);

    $output = stripAnsiColors($commandTester->getDisplay());

    expect($output)
        ->toContain('LaraDumps is searching for words used in debugging in: ' . sprintf('tests%sFixtures', DIRECTORY_SEPARATOR))
        ->and($output)
        ->not->toContain('Whoops. Specify the folders you need to search in --dir option in the comma separated')
        ->toContain('1/3')
        ->toContain(
            'dump(\'this is a function to check!\')',
            'dd(\'this is a function to check!\')',
            '//dd(\'this is a function to check!\')',
            'dd(\'this is a blade dd function to check!\')',
            '@dd(\'this is a blade dd directive to check!\')',
        )
        ->toContain('ERROR - Found 7 errors / 2 files');
});

it('checks command without "dump", "dd" works property', function () {
    $commandTester = startCommandApplication([
        '--dir'          => sprintf('tests%sFixtures', DIRECTORY_SEPARATOR),
        '--ignore-files' => vsprintf('tests%sFixtures%sds_env, tests%sFixtures%sAnotherFunctionsToCheck.php', [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR]),
    ]);

    $output = stripAnsiColors($commandTester->getDisplay());

    expect($output)
        ->toContain('LaraDumps is searching for words used in debugging in: ' . sprintf('tests%sFixtures', DIRECTORY_SEPARATOR))
        ->and($output)
        ->not->toContain('Whoops. Specify the folders you need to search in --dir option in the comma separated')
        ->toContain('1/3')
        ->toContain(
            'ds(\'this is a function to check!\');',
            ' @ds("this is a function to check!")',
            'ds(\'this is a blade function to check!\')',
            '@ds(\'this is a blade directive to check!\')',
        )
        ->toContain('ERROR - Found 4 errors / 2 files');
});
