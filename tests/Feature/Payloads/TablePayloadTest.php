<?php

use LaraDumps\LaraDumpsCore\Payloads\TablePayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('TablePayload', function () {
    it('extracts column names from row keys', function () {
        $content = (new TablePayload([['name' => 'Luan', 'age' => 35]]))->content();
        expect($content['fields'])->toBe(['name', 'age']);
    });

    it('all rows are included in values', function () {
        $data    = [['x' => 1], ['x' => 2], ['x' => 3]];
        $content = (new TablePayload($data))->content();
        expect($content['values'])->toHaveCount(3);
    });

    it('non-string values are JSON-encoded to prevent type loss', function () {
        $content = (new TablePayload([['active' => true, 'score' => 9.5, 'tags' => ['a', 'b']]]))->content();
        $row     = $content['values'][0];

        expect($row['active'])->toBe('true')
            ->and($row['score'])->toBe('9.5')
            ->and($row['tags'])->toBe('["a","b"]');
    });

    it('string values are passed through without encoding', function () {
        $content = (new TablePayload([['name' => 'Luan Freitas']]))->content();
        expect($content['values'][0]['name'])->toBe('Luan Freitas');
    });

    it('object rows are cast to array before processing', function () {
        $row     = (object) ['id' => 1, 'name' => 'test'];
        $content = (new TablePayload([$row]))->content();
        expect($content['fields'])->toBe(['id', 'name']);
    });

    it('name defaults to Table when omitted', function () {
        expect((new TablePayload([]))->content()['label'])->toBe('Table');
    });

    it('custom name is propagated to label', function () {
        expect((new TablePayload([], 'Orders'))->content()['label'])->toBe('Orders');
    });

    it('columns from later rows that are absent in earlier rows are also discovered', function () {
        $data    = [['a' => 1], ['a' => 2, 'b' => 3]];
        $content = (new TablePayload($data))->content();
        expect($content['fields'])->toContain('a')->and($content['fields'])->toContain('b');
    });
});
