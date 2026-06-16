<?php

use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\ValidJsonPayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('ValidJsonPayload', function () {
    it('produces a toArray() with type json_validate so the app triggers validation UI', function () {
        $payload = new ValidJsonPayload();
        $payload->setNotificationId('test-id');

        expect($payload->toArray()['type'])->toBe('json_validate');
    });

    it('carries no content — it is a marker that tells the app to validate the previous dump', function () {
        $payload = new ValidJsonPayload();
        $payload->setNotificationId('test-id');

        expect($payload->toArray()['json_validate'])->toBe([]);
    });

    it('isJson() sends this payload via LaraDumps', function () {
        $captured = null;
        LaraDumps::beforeSend(function ($p) use (&$captured) {
            $captured = $p;
        });
        (new LaraDumps())->isJson();
        LaraDumps::beforeSend(null);

        expect($captured)->toBeInstanceOf(ValidJsonPayload::class);
    });
});
