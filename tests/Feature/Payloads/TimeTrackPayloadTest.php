<?php

use LaraDumps\LaraDumpsCore\Payloads\TimeTrackPayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('TimeTrackPayload', function () {
    it('each instance generates a unique tracker_id', function () {
        $a = (new TimeTrackPayload('ref'))->content()['tracker_id'];
        $b = (new TimeTrackPayload('ref'))->content()['tracker_id'];

        expect($a)->not->toBe($b);
    });

    it('time is a float representing the current unix timestamp', function () {
        $before = microtime(true);
        $time   = (new TimeTrackPayload('ref'))->content()['time'];
        $after  = microtime(true);

        expect($time)->toBeFloat()
            ->and($time)->toBeGreaterThanOrEqual($before)
            ->and($time)->toBeLessThanOrEqual($after);
    });

    it('end_time is absent when stop is false', function () {
        expect((new TimeTrackPayload('ref', stop: false))->content())->not->toHaveKey('end_time');
    });

    it('end_time is present and a valid timestamp when stop is true', function () {
        $before  = microtime(true);
        $content = (new TimeTrackPayload('ref', stop: true))->content();
        $after   = microtime(true);

        expect($content)->toHaveKey('end_time')
            ->and($content['end_time'])->toBeGreaterThanOrEqual($before)
            ->and($content['end_time'])->toBeLessThanOrEqual($after);
    });

    it('withLabel uses the reference as the label so timers are identified by name', function () {
        expect((new TimeTrackPayload('checkout-flow'))->withLabel()->label)->toBe('checkout-flow');
    });
});
