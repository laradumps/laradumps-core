<?php

use Illuminate\Support\Collection;
use LaraDumps\LaraDumpsCore\Actions\ConvertArrayToPhpSyntax;
use PHPUnit\Framework\TestCase;

beforeEach(function () {
})->skipOnWindows();

uses(TestCase::class);

it('converts string to string unchanged', function (): void {
    $input  = 'simple text';
    $output = ConvertArrayToPhpSyntax::convert($input);

    expect($output)->toEqual($input);
});

it('converts empty array to PHP syntax', function (): void {
    $output = ConvertArrayToPhpSyntax::convert([]);
    expect($output)->toEqual('[
]');
});
it('converts array to valid PHP syntax', function (): void {
    $input = [
        'name'   => 'John',
        'age'    => 30,
        'active' => true,
        'tags'   => ['php', 'laravel'],
        'meta'   => null,
        'scores' => [95.5, 88.0],
    ];

    $output = ConvertArrayToPhpSyntax::convert($input);

    expect($output)->toContain("'name' => 'John'")
        ->and($output)->toContain("'age' => 30")
        ->and($output)->toContain("'active' => true")
        ->and($output)->toContain("'tags' => [")
        ->and($output)->toContain("0 => 'php'")
        ->and($output)->toContain("1 => 'laravel'")
        ->and($output)->toContain("'meta' => null")
        ->and($output)->toContain("'scores' => [")
        ->and($output)->toContain('0 => 95.5')
        ->and($output)->toMatch("/1 => 88(\.0)?/");
});

it('converts nested arrays with numeric keys', function (): void {
    $input = [
        'matrix' => [
            [1, 2],
            [3, 4],
        ],
    ];

    $expected = <<<'PHP'
[
    'matrix' => [
        0 => [
            0 => 1,
            1 => 2,
        ],
        1 => [
            0 => 3,
            1 => 4,
        ],
    ],
]
PHP;

    $output = ConvertArrayToPhpSyntax::convert($input);
    expect($output)->toEqual($expected);
});

it('converts Laravel Collection to PHP syntax', function (): void {
    $collection = new Collection([
        'item1' => 'value1',
        'item2' => ['subitem' => 123],
    ]);

    $expected = <<<'PHP'
[
    'item1' => 'value1',
    'item2' => [
        'subitem' => 123,
    ],
]
PHP;

    $output = ConvertArrayToPhpSyntax::convert($collection);
    expect($output)->toEqual($expected);
});

it('converts object with toArray method to PHP syntax', function (): void {
    $object = new class () {
        public function toArray(): array
        {
            return [
                'property' => 'value',
                'numbers'  => [1, 2, 3],
            ];
        }
    };

    $expected = <<<'PHP'
[
    'property' => 'value',
    'numbers' => [
        0 => 1,
        1 => 2,
        2 => 3,
    ],
]
PHP;

    $output = ConvertArrayToPhpSyntax::convert($object);
    expect($output)->toEqual($expected);
});

it('handles objects without toArray method', function (): void {
    $object              = new stdClass();
    $object->foo         = 'bar';
    $object->nested      = new stdClass();
    $object->nested->baz = 'qux';

    $array = [
        'object' => $object,
    ];

    $output = ConvertArrayToPhpSyntax::convert($array);

    $expected = <<<'PHP'
[
    'object' => (object) array(
   'foo' => 'bar',
   'nested' => 
  (object) array(
     'baz' => 'qux',
  ),
),
]
PHP;

    expect($output)->toEqual($expected);
});

it('handles null values correctly', function (): void {
    $input = [
        'key'    => null,
        'nested' => ['null_value' => null],
    ];

    $output = ConvertArrayToPhpSyntax::convert($input);

    expect($output)->toContain("'key' => null")
        ->and($output)->toContain("'null_value' => null");
});

it('handles boolean values correctly', function (): void {
    $input = [
        'true_value'  => true,
        'false_value' => false,
        'nested'      => ['bool' => false],
    ];

    $output = ConvertArrayToPhpSyntax::convert($input);

    expect($output)->toContain("'true_value' => true")
        ->and($output)->toContain("'false_value' => false")
        ->and($output)->toContain("'bool' => false");
});

it('handles numeric values correctly', function (): void {
    $input = [
        'integer'  => 42,
        'float'    => 3.14,
        'negative' => -10,
        'zero'     => 0,
    ];

    $output = ConvertArrayToPhpSyntax::convert($input);

    expect($output)->toContain("'integer' => 42")
        ->and($output)->toContain("'float' => 3.14")
        ->and($output)->toContain("'negative' => -10")
        ->and($output)->toContain("'zero' => 0");
});

it('handles mixed content', function (): void {
    $input = [
        'string' => 'text',
        'number' => 123,
        'bool'   => true,
        'null'   => null,
        'array'  => ['a', 'b'],
        'object' => new Collection(['x', 'y']),
    ];

    $output = ConvertArrayToPhpSyntax::convert($input);

    expect($output)->toContain("'string' => 'text'")
        ->and($output)->toContain("'number' => 123")
        ->and($output)->toContain("'bool' => true")
        ->and($output)->toContain("'null' => null")
        ->and($output)->toContain("0 => 'a'")
        ->and($output)->toContain("0 => 'x'");
});

it('handles new DateTimeInterface', function (): void {
    $dateTime = new DateTime('2023-10-01 12:00:00');

    $input = [
        'date' => $dateTime,
    ];

    $output = ConvertArrayToPhpSyntax::convert($input);

    expect($output)->toContain("'date' => '2023-10-01T12:00:00+00:00'");
});

it('handles new CarbonInterface', function (): void {
    $carbon = new DateTime('2023-10-01 12:00:00');

    $input = [
        'date' => $carbon,
    ];

    $output = ConvertArrayToPhpSyntax::convert($input);

    expect($output)->toContain("'date' => '2023-10-01T12:00:00+00:00'");
});
