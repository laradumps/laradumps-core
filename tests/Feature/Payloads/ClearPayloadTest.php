<?php

use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\ClearPayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('ClearPayload', function () {
    it('produces a toArray() with type clear so the app knows to wipe the screen', function () {
        $payload = new ClearPayload();
        $payload->setNotificationId('test-id');

        expect($payload->toArray()['type'])->toBe('clear');
    });

    it('toArray() carries no extra data under the clear key', function () {
        $payload = new ClearPayload();
        $payload->setNotificationId('test-id');

        expect($payload->toArray()['clear'])->toBe([]);
    });

    it('every send has a unique notification id', function () {
        $captured = [];

        LaraDumps::beforeSend(function ($p) use (&$captured) {
            $captured[] = $p->toArray()['id'];
        });

        (new LaraDumps())->clear();
        (new LaraDumps())->clear();

        LaraDumps::beforeSend(null);

        expect($captured[0])->not->toBe($captured[1]);
    });
});
