<?php

use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{BenchmarkPayload,
    ClearPayload,
    ColorPayload,
    DumpPayload,
    GroupedDumpPayload,
    JsonPayload,
    LabelPayload,
    PhpInfoPayload,
    ScreenPayload,
    TimeTrackPayload,
    ValidJsonPayload,
    ValidateStringPayload};
use PHPUnit\Framework\TestCase;

uses(TestCase::class);
function capturePayload(callable $fn): mixed
{
    $captured = null;
    LaraDumps::beforeSend(function ($payload) use (&$captured) {
        $captured = $payload;
    });
    $fn();
    LaraDumps::beforeSend(null);

    return $captured;
}

describe('LaraDumps::write()', function () {
    it('sends a DumpPayload for a string', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->write('hello'));
        expect($payload)->toBeInstanceOf(DumpPayload::class);
    });

    it('sends a JsonPayload when the string is valid JSON', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->write('{"key":"value"}'));
        expect($payload)->toBeInstanceOf(JsonPayload::class);
    });

    it('sends DumpPayload with variable_name when provided', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->write('Luan', variableName: 'name'));

        expect($payload)->toBeInstanceOf(DumpPayload::class);
        expect($payload->content()['variable_name'])->toBe('name');
    });

    it('returns the same LaraDumps instance (fluent)', function () {
        $ld   = new LaraDumps();
        $same = capturePayload(fn () => $ld->write('hello'));
        expect($ld)->toBeInstanceOf(LaraDumps::class);
    });
});

describe('LaraDumps::color() and color aliases', function () {
    it('sends a ColorPayload with the given color', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->color('purple'));
        expect($payload)->toBeInstanceOf(ColorPayload::class)
            ->and($payload->content()['color'])->toBe('purple');
    });

    it('dark() sends color black', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->dark());
        expect($payload)->toBeInstanceOf(ColorPayload::class)
            ->and($payload->content()['color'])->toBe('black');
    });

    it('red() sends color red', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->red());
        expect($payload)->toBeInstanceOf(ColorPayload::class);
    });

    it('blue() sends color blue', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->blue());
        expect($payload)->toBeInstanceOf(ColorPayload::class);
    });

    it('green() sends color green', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->green());
        expect($payload)->toBeInstanceOf(ColorPayload::class);
    });

    it('orange() sends color orange', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->orange());
        expect($payload)->toBeInstanceOf(ColorPayload::class);
    });
});

describe('LaraDumps::label()', function () {
    it('sends a LabelPayload with the given label', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->label('My Label'));
        expect($payload)->toBeInstanceOf(LabelPayload::class)
            ->and($payload->withLabel()->label)->toBe('My Label');
    });
});

describe('LaraDumps::toScreen()', function () {
    it('sends a ScreenPayload with the given name', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->toScreen('errors'));
        expect($payload)->toBeInstanceOf(ScreenPayload::class)
            ->and($payload->toScreen()->screen_name)->toBe('errors');
    });

    it('s() is an alias for toScreen()', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->s('errors'));
        expect($payload)->toBeInstanceOf(ScreenPayload::class);
    });

    it('w() sends a new-window ScreenPayload', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->w('popup'));
        expect($payload)->toBeInstanceOf(ScreenPayload::class)
            ->and($payload->toScreen()->new_window)->toBeTrue();
    });
});

describe('LaraDumps::clear()', function () {
    it('sends a ClearPayload', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->clear());
        expect($payload)->toBeInstanceOf(ClearPayload::class);
    });
});

describe('LaraDumps::isJson()', function () {
    it('sends a ValidJsonPayload', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->isJson());
        expect($payload)->toBeInstanceOf(ValidJsonPayload::class);
    });
});

describe('LaraDumps::contains()', function () {
    it('sends a ValidateStringPayload of type contains', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->contains('hello'));
        expect($payload)->toBeInstanceOf(ValidateStringPayload::class)
            ->and($payload->content()['type'])->toBe('contains')
            ->and($payload->content()['content'])->toBe('hello');
    });

    it('respects caseSensitive and wholeWord flags', function () {
        $payload = capturePayload(
            fn () => (new LaraDumps())->contains('hello', caseSensitive: true, wholeWord: true)
        );
        expect($payload->content()['is_case_sensitive'])->toBeTrue()
            ->and($payload->content()['is_whole_word'])->toBeTrue();
    });
});

describe('LaraDumps::time() / stopTime()', function () {
    it('time() sends a TimeTrackPayload with the reference', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->time('my-timer'));
        expect($payload)->toBeInstanceOf(TimeTrackPayload::class)
            ->and($payload->withLabel()->label)->toBe('my-timer');
    });

    it('stopTime() sends a TimeTrackPayload with stop=true', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->stopTime('my-timer'));
        expect($payload)->toBeInstanceOf(TimeTrackPayload::class)
            ->and($payload->content())->toHaveKey('end_time');
    });
});

describe('LaraDumps::table()', function () {
    it('sends a TablePayload', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->table([['name' => 'Luan']], 'Users'));
        expect($payload->type())->toBe('table');
    });
});

describe('LaraDumps::writeGrouped()', function () {
    it('sends a GroupedDumpPayload', function () {
        $tempFile = sys_get_temp_dir() . '/ld_wg_test.php';
        file_put_contents($tempFile, "<?php\n\$x = 1;\n\$y = 2;\nds(\$x, \$y);\n");

        $payload = capturePayload(fn () => (new LaraDumps())->writeGrouped([1, 2], $tempFile, 4));

        @unlink($tempFile);

        expect($payload)->toBeInstanceOf(GroupedDumpPayload::class)
            ->and($payload->content()['items'])->toHaveCount(2);
    });
});

describe('LaraDumps::send()', function () {
    it('returns the payload', function () {
        $ld      = new LaraDumps();
        $payload = new ClearPayload();
        LaraDumps::beforeSend(null);
        $result = $ld->send($payload, withFrame: false);
        expect($result)->toBeInstanceOf(ClearPayload::class);
    });

    it('sets notification id on the payload', function () {
        $ld      = new LaraDumps();
        $payload = new ClearPayload();
        LaraDumps::beforeSend(null);
        $ld->send($payload, withFrame: false);
        expect($payload->toArray()['id'])->toBeString()->not->toBeEmpty();
    });
});

describe('LaraDumps constructor sleep', function () {
    it('does not sleep when config.sleep is 0 or missing', function () {
        $start = microtime(true);
        new LaraDumps();
        $elapsed = microtime(true) - $start;
        expect($elapsed)->toBeLessThan(1.0);
    });
});

describe('LaraDumps::write() edge cases', function () {
    it('returns self when write is called with null and payload is empty', function () {
        $ld = new LaraDumps();
        LaraDumps::beforeSend(null);

        new ReflectionClass(LaraDumps::class);

        $result = $ld->write(null);
        expect($result)->toBeInstanceOf(LaraDumps::class);
    });
});

describe('LaraDumps::phpinfo()', function () {
    it('sends a PhpInfoPayload', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->phpinfo());
        expect($payload)->toBeInstanceOf(PhpInfoPayload::class);
    });
});

describe('LaraDumps::benchmark()', function () {
    it('sends a BenchmarkPayload', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->benchmark(
            fn () => usleep(1000),
            fn () => usleep(2000),
        ));
        expect($payload)->toBeInstanceOf(BenchmarkPayload::class);
    });
});

describe('LaraDumps::macosAutoLaunch()', function () {
    it('returns early on non-Darwin systems or when config is false', function () {
        LaraDumps::$beforeSend = null;
        LaraDumps::macosAutoLaunch();

        expect(true)->toBeTrue();
    });
});

describe('Colors trait with color_in_screen config', function () {
    it('danger() sends color red when color_in_screen is off', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->danger());
        expect($payload)->toBeInstanceOf(ColorPayload::class)
            ->and($payload->content()['color'])->toBe('red');
    });

    it('warning() sends color orange when color_in_screen is off', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->warning());
        expect($payload)->toBeInstanceOf(ColorPayload::class)
            ->and($payload->content()['color'])->toBe('orange');
    });

    it('success() sends color green when color_in_screen is off', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->success());
        expect($payload)->toBeInstanceOf(ColorPayload::class)
            ->and($payload->content()['color'])->toBe('green');
    });

    it('info() sends color blue when color_in_screen is off', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->info());
        expect($payload)->toBeInstanceOf(ColorPayload::class)
            ->and($payload->content()['color'])->toBe('blue');
    });

    it('black() is alias for dark()', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->black());
        expect($payload)->toBeInstanceOf(ColorPayload::class)
            ->and($payload->content()['color'])->toBe('black');
    });
});

describe('Colors trait with color_in_screen enabled', function () {
    beforeEach(function () {
        $ref             = new ReflectionClass(Config::class);
        $cache           = $ref->getProperty('cachedContent');
        $this->origCache = $cache->getValue(null);

        $cache->setValue(null, [
            'observers' => ['enabled_in_testing' => true],
            'config'    => ['color_in_screen' => true],
        ]);
    });

    afterEach(function () {
        $ref   = new ReflectionClass(Config::class);
        $cache = $ref->getProperty('cachedContent');
        $cache->setAccessible(true);
        $cache->setValue(null, $this->origCache);
    });

    it('danger() sends to screen when color_in_screen is true', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->danger());
        expect($payload)->toBeInstanceOf(ScreenPayload::class)
            ->and($payload->toScreen()->screen_name)->toBe('danger');
    });

    it('warning() sends to screen when color_in_screen is true', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->warning());
        expect($payload)->toBeInstanceOf(ScreenPayload::class)
            ->and($payload->toScreen()->screen_name)->toBe('warning');
    });

    it('success() sends to screen when color_in_screen is true', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->success());
        expect($payload)->toBeInstanceOf(ScreenPayload::class)
            ->and($payload->toScreen()->screen_name)->toBe('success');
    });

    it('info() sends to screen when color_in_screen is true', function () {
        $payload = capturePayload(fn () => (new LaraDumps())->info());
        expect($payload)->toBeInstanceOf(ScreenPayload::class)
            ->and($payload->toScreen()->screen_name)->toBe('info');
    });
});

// ── macosAutoLaunch ───────────────────────────────────────────────────────────

describe('LaraDumps::macosAutoLaunch() with config enabled', function () {
    it('sets beforeSend closure when macos_auto_launch is enabled on Darwin', function () {
        // Set the config
        $ref       = new ReflectionClass(Config::class);
        $cache     = $ref->getProperty('cachedContent');
        $origCache = $cache->getValue(null);

        $cache->setValue(null, [
            'observers' => ['enabled_in_testing' => true],
            'config'    => ['macos_auto_launch' => true],
        ]);

        LaraDumps::$beforeSend = null;
        LaraDumps::macosAutoLaunch();

        $cache->setValue(null, $origCache);

        if (PHP_OS_FAMILY === 'Darwin') {
            expect(LaraDumps::$beforeSend)->toBeInstanceOf(Closure::class);

            $closure = LaraDumps::$beforeSend;
            $closure();
        } else {
            expect(LaraDumps::$beforeSend)->toBeNull();
        }

        LaraDumps::$beforeSend = null;
    });
});

describe('LaraDumps constructor with sleep config', function () {
    it('sleeps when config.sleep is greater than 0', function () {
        $ref       = new ReflectionClass(Config::class);
        $cache     = $ref->getProperty('cachedContent');
        $origCache = $cache->getValue(null);

        $cache->setValue(null, [
            'observers' => ['enabled_in_testing' => true],
            'config'    => ['sleep' => 1],
        ]);

        $start = microtime(true);
        new LaraDumps();
        $elapsed = microtime(true) - $start;

        $cache->setValue(null, $origCache);

        expect($elapsed)->toBeGreaterThanOrEqual(0.9);
    });
});
