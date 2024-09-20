<?php

use LaraDumps\LaraDumpsCore\Actions\Table;

it('can generate a table')
    ->expect(fn ($data) => Table::make($data, 'my table'))->toBe(_table_dump())
    ->with([
        'array'      => [_table_data()],
        'object'     => [(object) _table_data()],
        'collection' => [collect(_table_data())],
        'generator'  => [_table_generator()],
    ]);

function _table_data(): array
{
    return [
        ['id' => 1, 'name' => 'Luan',    'email' => 'luan@laradumps.dev'],
        ['id' => 2, 'name' => 'Dan',     'email' => 'dan@laradumps.dev'],
        ['id' => 3, 'name' => 'Claudio', 'email' => 'claudio@laradumps.dev'],
        ['id' => 4, 'name' => 'Vitão',   'email' => 'vitao@laradumps.dev'],
        ['id' => 5, 'name' => 'Anand',   'email' => 'anand@laradumps.dev'],
    ];
}

function _table_generator(): iterable
{
    yield from _table_data();
}

function _table_dump(): array
{
    return [
        'fields' => [
            0 => 'id',
            1 => 'name',
            2 => 'email',
        ],
        'values' => [
            0 => [
                'id'    => '1',
                'name'  => 'Luan',
                'email' => 'luan@laradumps.dev',
            ],
            1 => [
                'id'    => '2',
                'name'  => 'Dan',
                'email' => 'dan@laradumps.dev',
            ],
            2 => [
                'id'    => '3',
                'name'  => 'Claudio',
                'email' => 'claudio@laradumps.dev',
            ],
            3 => [
                'id'    => '4',
                'name'  => 'Vitão',
                'email' => 'vitao@laradumps.dev',
            ],
            4 => [
                'id'    => '5',
                'name'  => 'Anand',
                'email' => 'anand@laradumps.dev',
            ],
        ],
        'header' => [
            0 => 'id',
            1 => 'name',
            2 => 'email',
        ],
        'label' => 'my table',
    ];
}
