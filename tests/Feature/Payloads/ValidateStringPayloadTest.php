<?php

use LaraDumps\LaraDumpsCore\Payloads\ValidateStringPayload;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('ValidateStringPayload', function () {
    it('content defaults to empty string when setContent is never called', function () {
        expect((new ValidateStringPayload('contains'))->content()['content'])->toBe('');
    });

    it('setContent() updates the content key', function () {
        $payload = (new ValidateStringPayload('contains'))->setContent('hello world');
        expect($payload->content()['content'])->toBe('hello world');
    });

    it('defaults to case-insensitive and not whole-word', function () {
        $content = (new ValidateStringPayload('contains'))->setContent('x')->content();
        expect($content['is_case_sensitive'])->toBeFalse()
            ->and($content['is_whole_word'])->toBeFalse();
    });

    it('setCaseSensitive(true) and setWholeWord(true) are independent flags', function () {
        $payload = (new ValidateStringPayload('contains'))
            ->setContent('foo')
            ->setCaseSensitive(true);

        expect($payload->content()['is_case_sensitive'])->toBeTrue()
            ->and($payload->content()['is_whole_word'])->toBeFalse();
    });

    it('setCaseSensitive(false) explicitly reverts sensitivity after setting it', function () {
        $payload = (new ValidateStringPayload('contains'))
            ->setCaseSensitive(true)
            ->setCaseSensitive(false);

        expect($payload->content()['is_case_sensitive'])->toBeFalse();
    });

    it('setters return self for fluent chaining', function () {
        $payload = new ValidateStringPayload('contains');
        expect($payload->setContent('x'))->toBe($payload)
            ->and($payload->setCaseSensitive())->toBe($payload)
            ->and($payload->setWholeWord())->toBe($payload);
    });

    it('preserves the validate type string in content', function () {
        expect((new ValidateStringPayload('not_contains'))->content()['type'])->toBe('not_contains');
    });
});
