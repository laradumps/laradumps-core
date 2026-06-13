<?php

use LaraDumps\LaraDumpsCore\Payloads\LabelPayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('LabelPayload', function () {
    it('withLabel carries the exact string passed to constructor', function () {
        expect((new LabelPayload('My Label'))->withLabel()->label)->toBe('My Label');
    });

    it('original_content is set to the label string for serialization', function () {
        $payload = new LabelPayload('Auth error');
        expect($payload->getOriginalContent())->toBe('Auth error');
    });

    it('label with special characters is preserved exactly', function () {
        $label = 'User: "João" — status=200';
        expect((new LabelPayload($label))->withLabel()->label)->toBe($label);
    });
});
