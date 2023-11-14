<?php

use LaraDumps\LaraDumpsCore\Actions\Support;

it('detects valid JSON', function () {
    $jsonString = '{"key": "value"}';
    $validate   = Support::isJson($jsonString);
    expect($validate)->toBe(true);
});

it('detects invalid JSON', function () {
    $invalidJsonString = 'invalid_json';
    $validate          = Support::isJson($invalidJsonString);
    expect($validate)->toBe(false);
});

it('detects non-string input', function () {
    $nonString = 123;
    $validate  = Support::isJson($nonString);
    expect($validate)->toBe(false);
});

it('detects empty string input', function () {
    $emptyString = '';
    $validate    = Support::isJson($emptyString);
    expect($validate)->toBe(false);
});

it('detects null input', function () {
    $nullValue = null;
    $validate  = Support::isJson($nullValue);
    expect($validate)->toBe(false);
});

it('detects associative array input', function () {
    // Test associative array input
    $associativeArray = ['key' => 'value'];
    $validate         = Support::isJson($associativeArray);
    expect($validate)->toBe(false);
});

it('detects numeric array input', function () {
    $numericArray = [1, 2, 3];
    $validate     = Support::isJson($numericArray);
    expect($validate)->toBe(false);
});

it('detects nested valid JSON', function () {
    $jsonString = '{"key": {"nested": "value"}}';
    $validate   = Support::isJson($jsonString);
    expect($validate)->toBe(true);
});

it('detects valid JSON with numeric keys', function () {
    $jsonString = '{"1": "one", "2": "two"}';
    $validate   = Support::isJson($jsonString);
    expect($validate)->toBe(true);
});

it('detects valid JSON with special characters', function () {
    $jsonString = '{"name": "John Doe", "email": "john.doe@example.com"}';
    $validate   = Support::isJson($jsonString);
    expect($validate)->toBe(true);
});

it('detects valid JSON with arrays of objects', function () {
    $jsonString = '[{"name": "John"}, {"name": "Doe"}]';
    $validate   = Support::isJson($jsonString);
    expect($validate)->toBe(true);
});

it('detects valid JSON with Unicode characters', function () {
    $jsonString = '{"name": "日本語"}';
    $validate   = Support::isJson($jsonString);
    expect($validate)->toBe(true);
});

it('detects valid JSON with escaped characters', function () {
    $jsonString = '{"message": "This is a \\"quoted\\" string."}';
    $validate   = Support::isJson($jsonString);
    expect($validate)->toBe(true);
});
