<?php

use LaraDumps\LaraDumpsCore\Payloads\JsonPayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('JsonPayload', function () {
    it('string and original_content are identical — the raw JSON is passed through unchanged', function () {
        $json    = '{"user":{"id":1,"name":"Luan"},"roles":["admin"]}';
        $content = (new JsonPayload($json))->content();

        expect($content['string'])->toBe($json)
            ->and($content['original_content'])->toBe($json);
    });

    it('does not re-encode or modify the JSON string', function () {
        $json = '{"key":"value with \"quotes\" and \\\\backslash"}';
        expect((new JsonPayload($json))->content()['string'])->toBe($json);
    });

    it('defaults screen to home', function () {
        expect((new JsonPayload('{}'))->toScreen()->screen_name)->toBe('home');
    });

    it('respects a custom screen', function () {
        expect((new JsonPayload('{}', screen: 'api'))->toScreen()->screen_name)->toBe('api');
    });
});
