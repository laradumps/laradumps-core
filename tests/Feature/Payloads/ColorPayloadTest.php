<?php

use LaraDumps\LaraDumpsCore\Payloads\ColorPayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('ColorPayload', function () {
    it('preserves the exact color string in content', function () {
        expect((new ColorPayload('purple'))->content()['color'])->toBe('purple');
    });

    it('original_content is set to the color so the app can display what was passed', function () {
        $payload = new ColorPayload('teal');
        expect($payload->getOriginalContent())->toBe('teal');
    });

    it('defaults screen to home', function () {
        expect((new ColorPayload('red'))->toScreen()->screen_name)->toBe('home');
    });

    it('respects a custom screen name', function () {
        expect((new ColorPayload('red', screen: 'errors'))->toScreen()->screen_name)->toBe('errors');
    });

    it('defaults label to empty string', function () {
        expect((new ColorPayload('red'))->withLabel()->label)->toBe('');
    });

    it('respects a custom label', function () {
        expect((new ColorPayload('red', label: 'warning'))->withLabel()->label)->toBe('warning');
    });
});
