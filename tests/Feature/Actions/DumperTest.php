<?php

use LaraDumps\LaraDumpsCore\Actions\Dumper;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('Dumper::dump()', function () {
    it('returns raw string for string input', function () {
        [$value, $id] = Dumper::dump('hello');

        expect($value)->toBe('hello')
            ->and($id)->toBeString()->not->toBeEmpty();
    });

    it('returns raw int for integer input', function () {
        [$value, $id] = Dumper::dump(42);

        expect($value)->toBe(42);
    });

    it('returns raw bool for boolean input', function () {
        [$value] = Dumper::dump(true);
        expect($value)->toBeTrue();

        [$value] = Dumper::dump(false);
        expect($value)->toBeFalse();
    });

    it('returns null for null input', function () {
        [$value] = Dumper::dump(null);
        expect($value)->toBeNull();
    });

    it('returns sf-dump HTML for arrays', function () {
        [$html, $id] = Dumper::dump(['key' => 'value']);

        expect($html)->toContain('sf-dump')
            ->and($html)->toContain('sf-dump-key')
            ->and($id)->toBeString()->not->toBeEmpty();
    });

    it('returns sf-dump HTML for objects', function () {
        $obj = (object) ['id' => 1];

        [$html, $id] = Dumper::dump($obj);

        expect($html)->toContain('sf-dump')
            ->and($id)->toBeString()->not->toBeEmpty();
    });
});

describe('Dumper::dump() with forceCloner', function () {
    it('returns sf-dump HTML for string when forceCloner is true', function () {
        [$html, $id] = Dumper::dump('hello', forceCloner: true);

        expect($html)->toContain('sf-dump')
            ->and($html)->toContain('hello')
            ->and($id)->toBeString()->not->toBeEmpty();
    });

    it('returns sf-dump HTML for integer when forceCloner is true', function () {
        [$html, $id] = Dumper::dump(42, forceCloner: true);

        expect($html)->toContain('sf-dump')
            ->and($html)->toContain('42')
            ->and($id)->toBeString()->not->toBeEmpty();
    });

    it('returns sf-dump HTML for boolean when forceCloner is true', function () {
        [$html, $id] = Dumper::dump(true, forceCloner: true);

        expect($html)->toContain('sf-dump')
            ->and($html)->toContain('true')
            ->and($id)->toBeString()->not->toBeEmpty();
    });

    it('returns sf-dump HTML for null when forceCloner is true', function () {
        [$html, $id] = Dumper::dump(null, forceCloner: true);

        expect($html)->toContain('sf-dump')
            ->and($id)->toBeString()->not->toBeEmpty();
    });

    it('sf-dump id matches between html and returned id', function () {
        [$html, $id] = Dumper::dump(['x' => 1], forceCloner: true);

        expect($html)->toContain("sf-dump-{$id}");
    });

    it('produces different ids for successive calls', function () {
        [, $id1] = Dumper::dump('a', forceCloner: true);
        [, $id2] = Dumper::dump('b', forceCloner: true);

        expect($id1)->not->toBe($id2);
    });
});
