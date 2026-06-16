<?php

use LaraDumps\LaraDumpsCore\Actions\VariableParser;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

// Fixture file used across tests — written once, kept as a known reference.
$fixtureFile = __DIR__ . '/../../Fixtures/variable-parser-fixture.php';

beforeAll(function () use ($fixtureFile) {
    file_put_contents($fixtureFile, <<<'PHP'
<?php
$name     = 'Luan';
$lastName = 'Freitas';
$age      = 35;
$address  = ['street' => 'Rua das Flores'];
$user     = (object) ['id' => 1];
ds($name, $lastName, $age, $address, $user);
PHP);
});

afterAll(function () use ($fixtureFile) {
    @unlink($fixtureFile);
});

describe('VariableParser::parse()', function () use ($fixtureFile) {
    it('extracts all variable names from a ds() call', function () use ($fixtureFile) {
        $result = VariableParser::parse($fixtureFile, 7, 5);

        expect($result)->toHaveCount(5)
            ->and($result[0]['name'])->toBe('name')
            ->and($result[1]['name'])->toBe('lastName')
            ->and($result[2]['name'])->toBe('age')
            ->and($result[3]['name'])->toBe('address')
            ->and($result[4]['name'])->toBe('user');
    });

    it('extracts correct declaration lines for each variable', function () use ($fixtureFile) {
        $result = VariableParser::parse($fixtureFile, 7, 5);

        expect($result[0]['line'])->toBe(2) // $name
            ->and($result[1]['line'])->toBe(3) // $lastName
            ->and($result[2]['line'])->toBe(4) // $age
            ->and($result[3]['line'])->toBe(5) // $address
            ->and($result[4]['line'])->toBe(6); // $user
    });

    it('returns a single variable name for single-arg call', function () use ($fixtureFile) {
        $result = VariableParser::parse($fixtureFile, 7, 1);

        expect($result)->toHaveCount(1)
            ->and($result[0]['name'])->toBe('name');
    });

    it('returns empty array for zero args', function () use ($fixtureFile) {
        $result = VariableParser::parse($fixtureFile, 7, 0);

        expect($result)->toBeEmpty();
    });

    it('returns fallback name for non-variable arguments', function () {
        $tempFile = sys_get_temp_dir() . '/vp_literal_test.php';
        file_put_contents($tempFile, "<?php\nds('literal', 42, true);\n");

        $result = VariableParser::parse($tempFile, 2, 3);

        expect($result[0]['name'])->toStartWith('arg')
            ->and($result[1]['name'])->toStartWith('arg')
            ->and($result[2]['name'])->toStartWith('arg');

        @unlink($tempFile);
    });

    it('handles non-existent file gracefully', function () {
        $result = VariableParser::parse('/non/existent/file.php', 1, 2);

        expect($result)->toBeArray();
    });
});
