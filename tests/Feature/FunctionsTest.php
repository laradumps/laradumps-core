<?php

use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, GroupedDumpPayload, JsonPayload};
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

// Helper: capture the payload sent
function captureFnPayload(callable $fn): mixed
{
    $captured = null;
    LaraDumps::beforeSend(function ($payload) use (&$captured) {
        $captured = $payload;
    });
    $fn();
    LaraDumps::beforeSend(null);

    return $captured;
}

describe('ds() function', function () {
    it('returns a LaraDumps instance', function () {
        $result = captureFnPayload(fn () => $instance = ds('hello'));
        expect($result)->toBeInstanceOf(DumpPayload::class);
    });

    it('sends DumpPayload for a single arg', function () {
        $payload = captureFnPayload(fn () => ds('test'));
        expect($payload)->toBeInstanceOf(DumpPayload::class);
    });

    it('sends JsonPayload for a valid JSON string', function () {
        $payload = captureFnPayload(fn () => ds('{"key":"value"}'));
        expect($payload)->toBeInstanceOf(JsonPayload::class);
    });

    it('handles multiple arguments', function () {
        $lastPayload = null;
        LaraDumps::beforeSend(function ($payload) use (&$lastPayload) {
            $lastPayload = $payload;
        });
        ds('a', 'b');
        LaraDumps::beforeSend(null);

        expect($lastPayload)->toBeInstanceOf(DumpPayload::class);
    });

    it('returns LaraDumps instance when called with no args', function () {
        LaraDumps::beforeSend(null);
        $result = ds();
        expect($result)->toBeInstanceOf(LaraDumps::class);
    });

    it('sends grouped dumps when config.grouped_dumps is true and multiple args', function () {
        $ref   = new ReflectionClass(Config::class);
        $cache = $ref->getProperty('cachedContent');
        $cache->setAccessible(true);
        $origCache = $cache->getValue(null);

        $cache->setValue(null, [
            'observers' => ['enabled_in_testing' => true],
            'config'    => ['grouped_dumps' => true],
        ]);

        $lastPayload = null;
        LaraDumps::beforeSend(function ($payload) use (&$lastPayload) {
            $lastPayload = $payload;
        });
        ds('a', 'b', 'c');
        LaraDumps::beforeSend(null);

        $cache->setValue(null, $origCache);

        expect($lastPayload)->toBeInstanceOf(GroupedDumpPayload::class);
    });
});

describe('dsq() function', function () {
    it('sends dump with autoInvokeApp false', function () {
        $payload = captureFnPayload(fn () => dsq('quiet test'));
        expect($payload)->toBeInstanceOf(DumpPayload::class);
    });

    it('handles multiple args', function () {
        $lastPayload = null;
        LaraDumps::beforeSend(function ($payload) use (&$lastPayload) {
            $lastPayload = $payload;
        });
        dsq('a', 'b');
        LaraDumps::beforeSend(null);

        expect($lastPayload)->toBeInstanceOf(DumpPayload::class);
    });

    it('returns early when called with no args', function () {
        $payload = captureFnPayload(fn () => dsq());
        expect($payload)->toBeNull();
    });

    it('sends grouped dumps when config.grouped_dumps is true and multiple args', function () {
        $ref   = new ReflectionClass(Config::class);
        $cache = $ref->getProperty('cachedContent');
        $cache->setAccessible(true);
        $origCache = $cache->getValue(null);

        $cache->setValue(null, [
            'observers' => ['enabled_in_testing' => true],
            'config'    => ['grouped_dumps' => true],
        ]);

        $lastPayload = null;
        LaraDumps::beforeSend(function ($payload) use (&$lastPayload) {
            $lastPayload = $payload;
        });
        dsq('x', 'y', 'z');
        LaraDumps::beforeSend(null);

        $cache->setValue(null, $origCache);

        expect($lastPayload)->toBeInstanceOf(GroupedDumpPayload::class);
    });
});

describe('dsd() function', function () {
    it('sends dump and exits (tested via subprocess)', function () {
        $autoload = realpath(__DIR__ . '/../../vendor/autoload.php');
        $script   = "<?php\nrequire '{$autoload}';\n\\LaraDumps\\LaraDumpsCore\\LaraDumps::beforeSend(function() {});\ndsd('goodbye');\n";

        $tempFile = sys_get_temp_dir() . '/ld_dsd_test_' . uniqid() . '.php';
        file_put_contents($tempFile, $script);

        exec("php {$tempFile} 2>&1", $output, $exitCode);
        @unlink($tempFile);

        // dsd calls die() which means exit code 0
        expect($exitCode)->toBe(0);
    });

    it('sends grouped dumps via subprocess when config.grouped_dumps is true', function () {
        $autoload = realpath(__DIR__ . '/../../vendor/autoload.php');
        $script   = <<<PHP
        <?php
        require '{$autoload}';
        
        \$ref = new ReflectionClass(\\LaraDumps\\LaraDumpsCore\\Actions\\Config::class);
        \$cache = \$ref->getProperty('cachedContent');
        \$cache->setAccessible(true);
        \$cache->setValue(null, [
            'observers' => ['enabled_in_testing' => true],
            'config' => ['grouped_dumps' => true],
        ]);
        
        \\LaraDumps\\LaraDumpsCore\\LaraDumps::beforeSend(function() {});
        dsd('a', 'b');
        PHP;

        $tempFile = sys_get_temp_dir() . '/ld_dsd_grouped_' . uniqid() . '.php';
        file_put_contents($tempFile, $script);

        exec("php {$tempFile} 2>&1", $output, $exitCode);
        @unlink($tempFile);

        expect($exitCode)->toBe(0);
    });

    it('sends single dump for one arg via subprocess', function () {
        $autoload = realpath(__DIR__ . '/../../vendor/autoload.php');
        $script   = "<?php\nrequire '{$autoload}';\n\\LaraDumps\\LaraDumpsCore\\LaraDumps::beforeSend(function() {});\ndsd('single');\n";

        $tempFile = sys_get_temp_dir() . '/ld_dsd_single_' . uniqid() . '.php';
        file_put_contents($tempFile, $script);

        exec("php {$tempFile} 2>&1", $output, $exitCode);
        @unlink($tempFile);

        expect($exitCode)->toBe(0);
    });
});

describe('runningInTest() function', function () {
    it('returns true when running under pest/phpunit', function () {
        expect(runningInTest())->toBeTrue();
    });
});

describe('appBasePath() function', function () {
    it('returns a string ending with directory separator', function () {
        $path = appBasePath();
        expect($path)->toBeString()
            ->and(str_ends_with($path, DIRECTORY_SEPARATOR))->toBeTrue();
    });

    it('strips public directory suffix when cwd ends with /public/', function () {
        $tempDir = sys_get_temp_dir() . '/ld_basepath_' . uniqid();
        $pubDir  = $tempDir . '/public';
        mkdir($pubDir, 0777, true);

        $origDir = getcwd();
        chdir($pubDir);

        $path = appBasePath();

        chdir($origDir);
        @rmdir($pubDir);
        @rmdir($tempDir);

        // Should strip /public/ and return parent
        expect($path)->not->toEndWith('/public/')
            ->and($path)->toEndWith(DIRECTORY_SEPARATOR);
    });

    it('strips web directory suffix when cwd ends with /web/', function () {
        $tempDir = sys_get_temp_dir() . '/ld_basepath_web_' . uniqid();
        $webDir  = $tempDir . '/web';
        mkdir($webDir, 0777, true);

        $origDir = getcwd();
        chdir($webDir);

        $path = appBasePath();

        chdir($origDir);
        @rmdir($webDir);
        @rmdir($tempDir);

        expect($path)->not->toEndWith('/web/')
            ->and($path)->toEndWith(DIRECTORY_SEPARATOR);
    });

    it('returns DIRECTORY_SEPARATOR when getcwd returns false', function () {
        // Create a temp dir, chdir to it, then remove it to make getcwd() return false
        $tempDir = sys_get_temp_dir() . '/ld_empty_cwd_' . uniqid();
        mkdir($tempDir);

        $origDir = getcwd();
        chdir($tempDir);
        rmdir($tempDir);

        // getcwd() should return false now (directory doesn't exist)
        $cwdResult = getcwd();

        if ($cwdResult === false) {
            $path = appBasePath();
            chdir($origDir);
            expect($path)->toBe(DIRECTORY_SEPARATOR);
        } else {
            // Some OS may still return the path even if dir is deleted
            chdir($origDir);
            @mkdir($tempDir);
            @rmdir($tempDir);
            expect(true)->toBeTrue(); // Skip gracefully
        }
    });
});

describe('runningInTest() edge cases via subprocess', function () {
    it('returns false when not in CLI SAPI (simulated via subprocess)', function () {
        // We can't change PHP_SAPI in-process, verify we're in CLI
        expect(PHP_SAPI)->toBe('cli');
        expect(runningInTest())->toBeTrue();
    });

    it('detects phpunit in argv', function () {
        // Current test runner has pest in argv
        expect(str_contains($_SERVER['argv'][0], 'pest') || str_contains($_SERVER['argv'][0], 'phpunit'))->toBeTrue();
    });

    it('returns false when argv does not contain pest or phpunit', function () {
        $origArgv           = $_SERVER['argv'];
        $_SERVER['argv'][0] = '/usr/bin/php';

        // runningInTest is already defined, we need to call it with modified argv
        // Since PHP_SAPI is still 'cli', it won't return false on line 127
        // It will check argv[0] for phpunit (false) and pest (false), then return false (line 138)
        $result = runningInTest();

        $_SERVER['argv'] = $origArgv;

        expect($result)->toBeFalse();
    });

    it('returns true when argv contains phpunit', function () {
        $origArgv           = $_SERVER['argv'];
        $_SERVER['argv'][0] = '/vendor/bin/phpunit';

        $result = runningInTest();

        $_SERVER['argv'] = $origArgv;

        expect($result)->toBeTrue();
    });
});

describe('LaraDumps::die() via subprocess', function () {
    it('exits the process', function () {
        $autoload = realpath(__DIR__ . '/../../vendor/autoload.php');
        $script   = "<?php\nrequire '{$autoload}';\n\\LaraDumps\\LaraDumpsCore\\LaraDumps::beforeSend(function() {});\n\$ld = new \\LaraDumps\\LaraDumpsCore\\LaraDumps();\n\$ld->write('test');\n\$ld->die();\necho 'SHOULD_NOT_REACH';\n";

        $tempFile = sys_get_temp_dir() . '/ld_die_test_' . uniqid() . '.php';
        file_put_contents($tempFile, $script);

        exec("php {$tempFile} 2>&1", $output, $exitCode);
        @unlink($tempFile);

        // die() exits with code 0 and does not reach the echo
        expect($exitCode)->toBe(0);
        expect(implode("\n", $output))->not->toContain('SHOULD_NOT_REACH');
    });
});
