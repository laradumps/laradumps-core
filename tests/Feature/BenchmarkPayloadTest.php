<?php

use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\BenchmarkPayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('BenchmarkPayload', function () {
    it('has type table_v2', function () {
        $payload = new BenchmarkPayload([fn () => 1 + 1]);
        expect($payload->type())->toBe('table_v2');
    });

    it('withLabel returns Benchmark by default', function () {
        $payload = new BenchmarkPayload([fn () => 1]);
        expect($payload->withLabel()->label)->toBe('Benchmark');
    });

    it('toScreen returns home by default', function () {
        $payload = new BenchmarkPayload([fn () => 1]);
        expect($payload->toScreen()->screen_name)->toBe('home');
    });

    it('content returns values key', function () {
        $payload = new BenchmarkPayload([fn () => 42]);
        $content = $payload->content();
        expect($content)->toHaveKey('values');
    });

    it('content includes Fastest key in values', function () {
        $payload = new BenchmarkPayload([fn () => 'a', fn () => 'b']);
        $content = $payload->content();
        expect($content['values'])->toHaveKey('Fastest');
    });

    it('measures multiple named closures', function () {
        $payload = new BenchmarkPayload([
            'alpha' => fn () => str_repeat('x', 100),
            'beta'  => fn () => implode('', array_fill(0, 100, 'x')),
        ]);
        $content = $payload->content();
        expect($content['values'])->toHaveKey('alpha')
            ->and($content['values'])->toHaveKey('beta');
    });

    it('unwraps single-array argument', function () {
        $closures = [
            'first'  => fn () => 1,
            'second' => fn () => 2,
        ];
        $payload = new BenchmarkPayload([$closures]);
        $content = $payload->content();
        expect($content['values'])->toHaveKey('first')
            ->and($content['values'])->toHaveKey('second');
    });
});

describe('LaraDumps::benchmark()', function () {
    it('sends a BenchmarkPayload', function () {
        $captured = null;
        LaraDumps::beforeSend(function ($payload) use (&$captured) {
            $captured = $payload;
        });

        (new LaraDumps())->benchmark(fn () => 1 + 1);

        LaraDumps::beforeSend(null);

        expect($captured)->toBeInstanceOf(BenchmarkPayload::class);
    });
});
