<?php

use LaraDumps\LaraDumpsCore\Payloads\PhpInfoPayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('PhpInfoPayload', function () {
    it('every row has both property and value keys', function () {
        $values = (new PhpInfoPayload())->content()['values'];

        foreach ($values as $row) {
            expect($row)->toHaveKeys(['property', 'value']);
        }
    });

    it('reports the six expected PHP environment properties', function () {
        $properties = array_column((new PhpInfoPayload())->content()['values'], 'property');

        expect($properties)->toContain('PHP version')
            ->and($properties)->toContain('Memory limit')
            ->and($properties)->toContain('Max file upload size')
            ->and($properties)->toContain('Max post size')
            ->and($properties)->toContain('ini file')
            ->and($properties)->toContain('Extensions');
    });

    it('PHP version matches the running process', function () {
        $values  = (new PhpInfoPayload())->content()['values'];
        $version = collect($values)->firstWhere('property', 'PHP version')['value'] ?? null;

        expect($version)->toBe(phpversion());
    });

    it('header labels columns as Property and Value', function () {
        expect((new PhpInfoPayload())->content()['header'])->toBe(['Property', 'Value']);
    });

    it('withLabel is always PHPINFO regardless of context', function () {
        expect((new PhpInfoPayload())->withLabel()->label)->toBe('PHPINFO');
    });
});
