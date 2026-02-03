<?php

use LaraDumps\LaraDumpsCore\Payloads\{
    BenchmarkPayload,
    ColorPayload,
    DumpPayload,
    JsonPayload,
    LabelPayload,
    ScreenPayload,
    TablePayload,
    TableV2Payload,
    TimeTrackPayload,
    ValidateStringPayload,
};

describe('original_content in Payloads', function () {
    it('DumpPayload sets and retrieves original_content', function () {
        $data    = ['name' => 'John', 'age' => 30];
        $payload = new DumpPayload($data, $data);
        expect($payload->getOriginalContent())->toBe($data);
    });

    it('JsonPayload sets and retrieves original_content', function () {
        $jsonString = '{"name": "John", "age": 30}';
        $payload    = new JsonPayload($jsonString);
        expect($payload->getOriginalContent())->toBe($jsonString);
    });

    it('TableV2Payload sets and retrieves original_content', function () {
        $data    = [['name' => 'John'], ['name' => 'Jane']];
        $payload = new TableV2Payload($data);
        expect($payload->getOriginalContent())->toBe($data);
    });

    it('TablePayload sets and retrieves original_content', function () {
        $data    = [['name' => 'John'], ['name' => 'Jane']];
        $payload = new TablePayload($data, 'Users');
        expect($payload->getOriginalContent())->toBe($data);
    });

    it('BenchmarkPayload sets and retrieves original_content', function () {
        $args    = ['test1' => fn () => 1 + 1, 'test2' => fn () => 2 + 2];
        $payload = new BenchmarkPayload($args);
        expect($payload->getOriginalContent())->toBe($args);
    });

    it('ColorPayload sets and retrieves original_content', function () {
        $color   = '#FF5733';
        $payload = new ColorPayload($color);
        expect($payload->getOriginalContent())->toBe($color);
    });

    it('TimeTrackPayload sets and retrieves original_content', function () {
        $reference = 'database_query';
        $payload   = new TimeTrackPayload($reference);
        expect($payload->getOriginalContent())->toBe($reference);
    });

    it('LabelPayload sets and retrieves original_content', function () {
        $label   = 'My Custom Label';
        $payload = new LabelPayload($label);
        expect($payload->getOriginalContent())->toBe($label);
    });

    it('ScreenPayload sets and retrieves original_content', function () {
        $screenName = 'dashboard';
        $payload    = new ScreenPayload($screenName);
        expect($payload->getOriginalContent())->toBe($screenName);
    });

    it('ValidateStringPayload sets and retrieves original_content', function () {
        $type    = 'email';
        $payload = new ValidateStringPayload($type);
        expect($payload->getOriginalContent())->toBe($type);
    });
});

describe('original_content edge cases', function () {
    it('handles null original_content', function () {
        $payload = new DumpPayload(['test' => 'data'], null);
        expect($payload->getOriginalContent())->toBeNull();
    });

    it('handles empty array original_content', function () {
        $payload = new DumpPayload(['test' => 'data'], []);
        expect($payload->getOriginalContent())->toBe([]);
    });

    it('handles scalar values', function () {
        $payload = new DumpPayload(['test' => 'data'], 'simple string');
        expect($payload->getOriginalContent())->toBe('simple string');
    });

    it('handles boolean values', function () {
        $payload = new DumpPayload(['test' => 'data'], true);
        expect($payload->getOriginalContent())->toBe(true);
    });

    it('handles numeric values', function () {
        $payload = new DumpPayload(['test' => 'data'], 42);
        expect($payload->getOriginalContent())->toBe(42);
    });

    it('handles arrays with numeric keys', function () {
        $data    = [1 => 'first', 2 => 'second', 3 => 'third'];
        $payload = new DumpPayload($data, $data);
        expect($payload->getOriginalContent())->toBe($data);
    });

    it('handles nested arrays', function () {
        $data    = ['user' => ['name' => 'John', 'email' => 'john@example.com'], 'active' => true];
        $payload = new DumpPayload($data, $data);
        expect($payload->getOriginalContent())->toBe($data);
    });

    it('handles objects with toArray method', function () {
        $data = new class () {
            public function toArray()
            {
                return ['converted' => true];
            }
        };
        $payload = new DumpPayload($data, $data);
        expect($payload->getOriginalContent())->toBe($data);
    });
});

describe('setOriginalContent method', function () {
    it('allows setting original_content after instantiation', function () {
        $payload = new DumpPayload(['initial' => 'data'], ['initial' => 'data']);
        expect($payload->getOriginalContent())->toBe(['initial' => 'data']);

        $payload->setOriginalContent(['updated' => 'data']);
        expect($payload->getOriginalContent())->toBe(['updated' => 'data']);
    });

    it('allows updating original_content to null', function () {
        $payload = new DumpPayload(['test' => 'data'], ['test' => 'data']);
        expect($payload->getOriginalContent())->not->toBeNull();

        $payload->setOriginalContent(null);
        expect($payload->getOriginalContent())->toBeNull();
    });

    it('allows changing original_content type', function () {
        $payload = new DumpPayload(['test' => 'data'], ['initial' => 'array']);

        $payload->setOriginalContent('string value');
        expect($payload->getOriginalContent())->toBe('string value');

        $payload->setOriginalContent(123);
        expect($payload->getOriginalContent())->toBe(123);

        $payload->setOriginalContent(true);
        expect($payload->getOriginalContent())->toBe(true);
    });

    it('works with JsonPayload', function () {
        $payload = new JsonPayload('{"initial": "json"}');
        expect($payload->getOriginalContent())->toBe('{"initial": "json"}');

        $payload->setOriginalContent('{"updated": "json"}');
        expect($payload->getOriginalContent())->toBe('{"updated": "json"}');
    });

    it('works with TableV2Payload', function () {
        $initialData = [['id' => 1]];
        $payload     = new TableV2Payload($initialData);
        expect($payload->getOriginalContent())->toBe($initialData);

        $updatedData = [['id' => 2], ['id' => 3]];
        $payload->setOriginalContent($updatedData);
        expect($payload->getOriginalContent())->toBe($updatedData);
    });

    it('works with all payload types', function () {
        $payloads = [
            'ColorPayload'          => new ColorPayload('#FF0000'),
            'TimeTrackPayload'      => new TimeTrackPayload('query'),
            'LabelPayload'          => new LabelPayload('label'),
            'ScreenPayload'         => new ScreenPayload('screen'),
            'ValidateStringPayload' => new ValidateStringPayload('email'),
        ];

        foreach ($payloads as $name => $payload) {
            $newValue = "updated-$name";
            $payload->setOriginalContent($newValue);
            expect($payload->getOriginalContent())->toBe($newValue, "Failed for $name");
        }
    });
});
