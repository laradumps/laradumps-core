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

describe('circular references in original_content', function () {
    it('handles DumpPayload with circular reference objects', function () {
        $obj       = new \stdClass();
        $obj->name = 'Test Object';
        $obj->self = $obj;

        $payload = new DumpPayload(['test' => 'data'], $obj);
        $payload->setFrame(['file' => 'test.php', 'line' => 1]);
        $payload->setNotificationId('test-id');

        $arrayPayload = $payload->toArray();

        expect($arrayPayload)->toHaveKey('dump')
            ->and($arrayPayload['dump'])->toHaveKey('original_content')
            ->and($arrayPayload['dump']['original_content'])->toBeObject();
    });

    it('handles TableV2Payload with circular reference objects in data', function () {
        $obj       = new \stdClass();
        $obj->id   = 1;
        $obj->self = $obj;

        $data = [
            ['name' => 'Item 1', 'object' => $obj],
            ['name' => 'Item 2', 'object' => $obj],
        ];

        $payload = new TableV2Payload($data);
        $payload->setFrame(['file' => 'test.php', 'line' => 1]);
        $payload->setNotificationId('test-id');

        $arrayPayload = $payload->toArray();

        expect($arrayPayload)->toHaveKey('table_v2');
    });

    it('handles mutual circular references between objects', function () {
        $obj1 = new \stdClass();
        $obj2 = new \stdClass();

        $obj1->name    = 'Object 1';
        $obj1->partner = $obj2;

        $obj2->name    = 'Object 2';
        $obj2->partner = $obj1;

        $payload = new DumpPayload(['test' => 'data'], $obj1);
        $payload->setFrame(['file' => 'test.php', 'line' => 1]);
        $payload->setNotificationId('test-id');

        $arrayPayload = $payload->toArray();

        expect($arrayPayload)->toHaveKey('dump');
        expect($arrayPayload['dump'])->toHaveKey('original_content');
    });

    it('handles objects with toArray method containing circular references', function () {
        $obj = new class () {
            public function toArray()
            {
                $data         = ['id' => 1];
                $data['self'] = $this;

                return $data;
            }
        };

        $payload = new DumpPayload(['test' => 'data'], $obj);
        $payload->setFrame(['file' => 'test.php', 'line' => 1]);
        $payload->setNotificationId('test-id');

        $arrayPayload = $payload->toArray();

        expect($arrayPayload)->toHaveKey('dump');
    });

    it('payload toArray does not exhaust memory with complex circular structures', function () {
        $root     = new \stdClass();
        $root->id = 'root';

        $child1         = new \stdClass();
        $child1->id     = 'child1';
        $child1->parent = $root;
        $child1->self   = $child1;

        $child2          = new \stdClass();
        $child2->id      = 'child2';
        $child2->parent  = $root;
        $child2->sibling = $child1;

        $root->children = [$child1, $child2];

        $payload = new DumpPayload(['root' => $root], $root);
        $payload->setFrame(['file' => 'test.php', 'line' => 1]);
        $payload->setNotificationId('test-id');

        $arrayPayload = $payload->toArray();

        expect($arrayPayload)->toBeArray()
            ->and($arrayPayload)->toHaveKey('dump');
    });

    it('handles Exception objects which have circular references', function () {
        try {
            throw new \Exception('Test exception');
        } catch (\Exception $e) {
            $payload = new DumpPayload(['error' => 'occurred'], $e);
            $payload->setFrame(['file' => 'test.php', 'line' => 1]);
            $payload->setNotificationId('test-id');

            $arrayPayload = $payload->toArray();

            expect($arrayPayload)->toBeArray()
                ->and($arrayPayload)->toHaveKey('dump');
        }
    });

    it('handles arrays with multiple references to the same object', function () {
        $shared       = new \stdClass();
        $shared->name = 'Shared Object';

        $data = [
            'first'  => $shared,
            'second' => $shared,
            'nested' => [
                'third' => $shared,
            ],
        ];

        $payload = new DumpPayload(['test' => 'data'], $data);
        $payload->setFrame(['file' => 'test.php', 'line' => 1]);
        $payload->setNotificationId('test-id');

        $arrayPayload = $payload->toArray();

        expect($arrayPayload)->toBeArray()
            ->and($arrayPayload)->toHaveKey('dump');
    });
});
