<?php

use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, GroupedDumpPayload};
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

describe('DumpPayload with variable_name', function () {
    it('includes variable_name in content()', function () {
        [$pre, $id] = Dumper::dump('Luan');
        $payload    = new DumpPayload($pre, 'Luan', variableType: 'string', variableName: 'name');

        expect($payload->content())
            ->toHaveKey('variable_name', 'name')
            ->toHaveKey('variable_type', 'string');
    });

    it('variable_name is null when not provided', function () {
        [$pre, $id] = Dumper::dump('Luan');
        $payload    = new DumpPayload($pre, 'Luan', variableType: 'string');

        expect($payload->content()['variable_name'])->toBeNull();
    });

    it('is included in toArray() under the dump key', function () {
        [$pre, $id] = Dumper::dump(['x' => 1]);
        $payload    = new DumpPayload($pre, ['x' => 1], variableType: 'array', variableName: 'myArray');

        $ld   = new LaraDumps();
        $sent = $ld->send($payload, withFrame: false)->toArray();

        expect($sent['dump']['variable_name'])->toBe('myArray')
            ->and($sent['type'])->toBe('dump');
    });
});

describe('GroupedDumpPayload', function () {
    it('has type dump_group', function () {
        $payload = new GroupedDumpPayload([]);
        expect($payload->type())->toBe('dump_group');
    });

    it('content returns items array', function () {
        $items = [
            ['name' => 'name', 'line' => 5, 'sf_dump_id' => 'abc', 'dump' => '<pre>', 'original_content' => 'Luan', 'variable_type' => 'string'],
            ['name' => 'age',  'line' => 6, 'sf_dump_id' => 'def', 'dump' => '<pre>', 'original_content' => '35',   'variable_type' => 'integer'],
        ];

        $payload = new GroupedDumpPayload($items);

        expect($payload->content()['items'])->toHaveCount(2)
            ->and($payload->content()['items'][0]['name'])->toBe('name')
            ->and($payload->content()['items'][1]['name'])->toBe('age');
    });

    it('is included in toArray() under dump_group key', function () {
        $items   = [['name' => 'x', 'line' => 1, 'sf_dump_id' => 'id1', 'dump' => '<pre>', 'original_content' => '1', 'variable_type' => 'integer']];
        $payload = new GroupedDumpPayload($items);

        $ld   = new LaraDumps();
        $sent = $ld->send($payload, withFrame: false)->toArray();

        expect($sent['type'])->toBe('dump_group')
            ->and($sent['dump_group']['items'])->toHaveCount(1);
    });
});

describe('LaraDumps::writeGrouped()', function () {
    it('produces a GroupedDumpPayload with correct item count', function () {
        $captured = null;

        LaraDumps::beforeSend(function ($payload) use (&$captured) {
            $captured = $payload;
        });

        $tempFile = sys_get_temp_dir() . '/grouped_test.php';
        file_put_contents($tempFile, "<?php\n\$a = 1;\n\$b = 2;\nds(\$a, \$b);\n");

        $ld = new LaraDumps();
        $ld->writeGrouped([1, 2], $tempFile, 4);

        LaraDumps::beforeSend(null);
        @unlink($tempFile);

        expect($captured)->toBeInstanceOf(GroupedDumpPayload::class);

        $content = $captured->content();
        expect($content['items'])->toHaveCount(2)
            ->and($content['items'][0]['name'])->toBe('a')
            ->and($content['items'][1]['name'])->toBe('b');
    });

    it('each item has sf-dump HTML even for primitives', function () {
        $captured = null;

        LaraDumps::beforeSend(function ($payload) use (&$captured) {
            $captured = $payload;
        });

        $tempFile = sys_get_temp_dir() . '/grouped_primitives.php';
        file_put_contents($tempFile, "<?php\n\$str = 'hello';\n\$num = 42;\nds(\$str, \$num);\n");

        $ld = new LaraDumps();
        $ld->writeGrouped(['hello', 42], $tempFile, 4);

        LaraDumps::beforeSend(null);
        @unlink($tempFile);

        $items = $captured->content()['items'];

        expect($items[0]['dump'])->toContain('sf-dump')
            ->and($items[1]['dump'])->toContain('sf-dump');
    });

    it('each item sf_dump_id matches the id in the dump HTML', function () {
        $captured = null;

        LaraDumps::beforeSend(function ($payload) use (&$captured) {
            $captured = $payload;
        });

        $tempFile = sys_get_temp_dir() . '/grouped_id_check.php';
        file_put_contents($tempFile, "<?php\n\$x = [1, 2];\nds(\$x);\n");

        $ld = new LaraDumps();
        $ld->writeGrouped([[1, 2]], $tempFile, 3);

        LaraDumps::beforeSend(null);
        @unlink($tempFile);

        $item = $captured->content()['items'][0];

        expect($item['dump'])->toContain("sf-dump-{$item['sf_dump_id']}");
    });
});
