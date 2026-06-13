<?php

use LaraDumps\LaraDumpsCore\Payloads\ScreenPayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('ScreenPayload', function () {
    it('toScreen maps name directly to screen_name', function () {
        $screen = (new ScreenPayload('errors'))->toScreen();
        expect($screen->screen_name)->toBe('errors');
    });

    it('raiseIn defaults to 0 when not specified', function () {
        expect((new ScreenPayload('logs'))->toScreen()->raise_in)->toBe(0);
    });

    it('newWindow defaults to false when not specified', function () {
        expect((new ScreenPayload('logs'))->toScreen()->new_window)->toBeFalse();
    });

    it('raiseIn is forwarded to the Screen object', function () {
        expect((new ScreenPayload('logs', raiseIn: 5))->toScreen()->raise_in)->toBe(5);
    });

    it('newWindow is forwarded to the Screen object', function () {
        expect((new ScreenPayload('popup', newWindow: true))->toScreen()->new_window)->toBeTrue();
    });

    it('original_content captures the screen name for serialization', function () {
        expect((new ScreenPayload('dashboard'))->getOriginalContent())->toBe('dashboard');
    });
});
