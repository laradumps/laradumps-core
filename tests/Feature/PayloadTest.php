<?php

use LaraDumps\LaraDumpsCore\Actions\{Dumper};
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, TableV2Payload};
use LaraDumps\LaraDumpsCore\Tests\Feature\ClassWithException;

it('should return the correct payload to dump', function () {
    $args = [
        'name' => 'Luan',
    ];

    [$args, $id] = Dumper::dump($args);

    $frame = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps();
    $payload   = new DumpPayload($args);
    $payload->setFrame($frame);

    $payload = $laradumps->send($payload, withFrame: false)->toArray();

    expect($payload)
        ->id->toBeUuid()
        ->type->toBe('dump')
        ->code_snippet->toBeArray()
        ->and($payload['ide_handle']['real_path'])
        ->toBe('Test')
        ->and($payload['ide_handle']['line'])
        ->toBe('1')
        ->and($payload['dump']['dump'])
        ->toContain(
            '<span class=sf-dump-key>name</span>',
            '<span class=sf-dump-str title="4 characters">Luan</span>'
        );
});

it('should return the correct payload to table_v2', function () {
    $data = [
        'Name'  => 'Anand Pilania',
        'Email' => 'pilaniaanand@gmail.com',
        'Stack' => [
            'Laravel',
            'Flutter',
        ],
    ];

    $frame = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps();
    $payload   = new TableV2Payload($data);
    $payload->setFrame($frame);

    $payload = $laradumps->send($payload)->toArray();

    expect($payload)
        ->id->toBeUuid()
        ->type->toBe('table_v2')
        ->and($payload['table_v2']['values']['Name'])
        ->toContain('Anand Pilania')
        ->and($payload['table_v2']['values']['Email'])
        ->toContain('pilaniaanand@gmail.com')
        ->and($payload['table_v2']['values']['Stack'][0])
        ->toContain('Laravel');
})->group('table_v2');

it('code snippet work properly - between 6-6', function () {
    $class = new ClassWithException();

    $context = $class->handleCodeSnippet(6, 6);

    expect($context[0])
        ->toHaveKeys(['file', 'line', 'snippet'])
        ->and($context[0])
        ->file->toContain(adjustPathToDirectorySeparator('tests/Feature/ClassWithException.php'))
        ->line->toBe(16)
        ->and($context[0])
        ->snippet->toBe([
            10 => "    {",
            11 => "        \$this->handleCodeSnippet();",
            12 => "    }",
            13 => "",
            14 => '    public function handleCodeSnippet(int $linesAbove = 6, int $linesBelow = 6)',
            15 => "    {",
            16 => "        \$exception = new \Exception('Error!');",
            17 => "",
            18 => '        return (new CodeSnippet($linesAbove, $linesBelow))->fromException($exception);',
            19 => "    }",
            20 => "}",
        ]);
});

it('code snippet work properly - between 10-4', function () {
    $class = new ClassWithException();

    $context = $class->handleCodeSnippet(10, 4);

    expect($context[0])
        ->toHaveKeys(['file', 'line', 'snippet'])
        ->and($context[0])
        ->file->toContain(adjustPathToDirectorySeparator('tests/Feature/ClassWithException.php'))
        ->line->toBe(16)
        ->and($context[0])
        ->snippet->toBe([
            6  => '',
            7  => 'class ClassWithException',
            8  => '{',
            9  => '    public function __construct()',
            10 => '    {',
            11 => '        $this->handleCodeSnippet();',
            12 => '    }',
            13 => '',
            14 => '    public function handleCodeSnippet(int $linesAbove = 6, int $linesBelow = 6)',
            15 => '    {',
            16 => '        $exception = new \Exception(\'Error!\');',
            17 => '',
            18 => '        return (new CodeSnippet($linesAbove, $linesBelow))->fromException($exception);',
            19 => '    }',
            20 => '}',
        ]);
});

it('code snippet work properly - second Code Snippet file contents', function () {
    $class = new ClassWithException();

    $context = $class->handleCodeSnippet(10, 4);

    expect($context[1])
        ->toHaveKeys(['file', 'line', 'snippet'])
        ->and($context[1])
        ->file->toContain(adjustPathToDirectorySeparator('tests/Feature/PayloadTest.php'))
        ->line->toBe(132)
        ->and($context[1])
        ->snippet->toHaveCount(15);
});

it('setCodeSnippet stores and includes code_snippet in toArray', function () {
    $laradumps = new LaraDumps();
    $payload   = new DumpPayload('test');
    $payload->setFrame(['file' => 'Test.php', 'line' => 1]);

    $snippet = [
        ['file' => 'Test.php', 'line' => 10, 'snippet' => [9 => 'line 9', 10 => 'line 10', 11 => 'line 11']],
    ];
    $payload->setCodeSnippet($snippet);
    $payload->setNotificationId('test-id');

    $array = $payload->toArray();
    expect($array['code_snippet'])->toBe($snippet);
});

it('getExtraPayload returns empty array when Laravel is not available', function () {
    $payload = new DumpPayload('test');
    $payload->setFrame(['file' => 'Test.php', 'line' => 1]);
    $payload->setNotificationId('test-id');

    $array = $payload->toArray();
    // Without Laravel, extra should be empty array
    expect($array['extra'])->toBe([]);
});

it('toArray includes original_content when set', function () {
    $payload = new DumpPayload('test', 'original');
    $payload->setFrame(['file' => 'Test.php', 'line' => 1]);
    $payload->setNotificationId('test-id');

    $array = $payload->toArray();
    expect($array['dump'])->toHaveKey('original_content');
});

it('toArray does not add base original_content when originalContent property is null', function () {
    // Use a ClearPayload which has no originalContent set
    $payload = new \LaraDumps\LaraDumpsCore\Payloads\ClearPayload();
    $payload->setFrame(['file' => 'Test.php', 'line' => 1]);
    $payload->setNotificationId('test-id');

    $array = $payload->toArray();
    // ClearPayload content should not have 'original_content' key added by base Payload
    expect($array['clear'])->not->toHaveKey('original_content');
});
