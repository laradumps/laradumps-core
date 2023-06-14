<?php

use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, TableV2Payload};
use LaraDumps\LaraDumpsCore\Support\Dumper;
use Ramsey\Uuid\Uuid;

beforeEach(function () {
    putenv('DS_RUNNING_IN_TESTS=true');
});

it('should return the correct payload to dump', function () {
    fixtureEnv('ds_env');

    $args = [
        'name' => 'Luan',
    ];

    [$args, $id]    = Dumper::dump($args);
    $notificationId = Uuid::uuid4()->toString();

    $trace = [
        'file' => 'Test',
        'line' => 1,
    ];

    $laradumps = new LaraDumps(notificationId: $notificationId, trace: $trace);
    $payload   = $laradumps->send(new DumpPayload($args))->toArray();

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('dump')
        ->ide_handle->toMatchArray([
            'handler' => 'phpstorm://open?file=Test&line=1',
            'path'    => 'Test',
            'line'    => 1,
        ])
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

    $trace = [
        'file' => 'Test',
        'line' => 1,
    ];

    $notificationId = Uuid::uuid4()->toString();

    $laradumps = new LaraDumps($notificationId, trace: $trace);
    $payload   = $laradumps->send(new TableV2Payload($data))->toArray();

    expect($payload)
        ->id->toBe($notificationId)
        ->type->toBe('table_v2')
        ->and($payload['table_v2']['values']['Name'])
        ->toContain('Anand Pilania')
        ->and($payload['table_v2']['values']['Email'])
        ->toContain('pilaniaanand@gmail.com')
        ->and($payload['table_v2']['values']['Stack'][0])
        ->toContain('Laravel');
})->group('table_v2');
