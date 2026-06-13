<?php

use LaraDumps\LaraDumpsCore\Actions\Config;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

// Helpers to create/remove a temp config file and redirect Config to it
function withTempConfig(array $content, callable $fn): void
{
    $dir = sys_get_temp_dir() . '/laradumps_config_test_' . uniqid();
    mkdir($dir);
    $file = $dir . '/laradumps.yaml';

    file_put_contents($file, \Symfony\Component\Yaml\Yaml::dump($content));

    // Patch configFilePath via reflection
    $ref  = new ReflectionClass(Config::class);
    $path = $ref->getProperty('configFilePath');
    $path->setAccessible(true);
    $cache = $ref->getProperty('cachedContent');
    $cache->setAccessible(true);

    $originalPath  = $path->isInitialized() ? $path->getValue(null) : null;
    $originalCache = $cache->getValue(null);

    $path->setValue(null, $file);
    $cache->setValue(null, null);

    try {
        $fn($file, $dir);
    } finally {
        // Restore
        if ($originalPath !== null) {
            $path->setValue(null, $originalPath);
        }
        $cache->setValue(null, $originalCache);
        @unlink($file);
        @rmdir($dir);
    }
}

describe('Config::get()', function () {
    it('returns default when key does not exist', function () {
        withTempConfig(['observers' => ['enabled_in_testing' => true], 'app' => ['port' => 9191]], function () {
            expect(Config::get('config.missing', 'default_val'))->toBe('default_val');
        });
    });

    it('returns nested value by dot notation', function () {
        withTempConfig([
            'observers' => ['enabled_in_testing' => true],
            'config'    => ['sleep' => 5],
        ], function () {
            expect(Config::get('config.sleep', 0))->toBe(5);
        });
    });

    it('returns false in test context when enabled_in_testing is false', function () {
        withTempConfig([
            'observers' => ['enabled_in_testing' => false],
            'config'    => ['sleep' => 5],
        ], function () {
            // runningInTest() returns true in phpunit context
            expect(Config::get('config.sleep', 0))->toBeFalse();
        });
    });
});

describe('Config::exists()', function () {
    it('returns true when config file exists', function () {
        withTempConfig(['observers' => ['enabled_in_testing' => true], 'app' => []], function () {
            expect(Config::exists())->toBeTrue();
        });
    });

    it('returns false when config file does not exist', function () {
        $ref   = new ReflectionClass(Config::class);
        $path  = $ref->getProperty('configFilePath');
        $cache = $ref->getProperty('cachedContent');
        $path->setAccessible(true);
        $cache->setAccessible(true);

        $orig      = $path->isInitialized() ? $path->getValue(null) : null;
        $origCache = $cache->getValue(null);

        $path->setValue(null, '/non/existent/path/laradumps.yaml');
        $cache->setValue(null, null);

        $result = Config::exists();

        if ($orig !== null) {
            $path->setValue(null, $orig);
        }
        $cache->setValue(null, $origCache);

        expect($result)->toBeFalse();
    });
});

describe('Config::set()', function () {
    it('sets and persists a nested value', function () {
        withTempConfig([
            'observers' => ['enabled_in_testing' => true],
            'config'    => ['sleep' => 0],
        ], function () {
            Config::set('config.sleep', 3);
            expect(Config::get('config.sleep'))->toBe(3);
        });
    });

    it('creates intermediate keys when they do not exist', function () {
        withTempConfig([
            'observers' => ['enabled_in_testing' => true],
        ], function () {
            Config::set('new_section.new_key', 'value');
            expect(Config::get('new_section.new_key'))->toBe('value');
        });
    });
});

describe('Config::publish()', function () {
    it('returns true and writes project_path on success', function () {
        $srcFile = sys_get_temp_dir() . '/ld_publish_src_' . uniqid() . '.yaml';
        file_put_contents($srcFile, \Symfony\Component\Yaml\Yaml::dump([
            'observers' => ['enabled_in_testing' => true],
            'app'       => ['port' => 9191],
        ]));

        withTempConfig(['observers' => ['enabled_in_testing' => true]], function () use ($srcFile) {
            $result = Config::publish('/my/project/', $srcFile);
            expect($result)->toBeTrue();
            expect(Config::get('app.project_path'))->toBe('/my/project/');
        });

        @unlink($srcFile);
    });

    it('returns false when source file does not exist', function () {
        withTempConfig(['observers' => ['enabled_in_testing' => true]], function () {
            expect(Config::publish('/pwd/', '/non/existent/file.yaml'))->toBeFalse();
        });
    });
});

describe('Config::loadConfig() with invalid YAML', function () {
    it('returns empty array when YAML file is malformed (ParseException)', function () {
        $ref   = new ReflectionClass(Config::class);
        $path  = $ref->getProperty('configFilePath');
        $cache = $ref->getProperty('cachedContent');
        $path->setAccessible(true);
        $cache->setAccessible(true);

        $orig      = $path->isInitialized() ? $path->getValue(null) : null;
        $origCache = $cache->getValue(null);

        // Create a file with invalid YAML
        $dir = sys_get_temp_dir() . '/laradumps_invalid_yaml_' . uniqid();
        mkdir($dir);
        $file = $dir . '/laradumps.yaml';
        file_put_contents($file, "invalid: yaml: [\nbroken");

        $path->setValue(null, $file);
        $cache->setValue(null, null);

        // This should trigger ParseException, config becomes [].
        // Since enabled_in_testing defaults to false and runningInTest() is true,
        // the get() returns false for missing keys in testing context.
        $result = Config::get('any.key', 'fallback');

        if ($orig !== null) {
            $path->setValue(null, $orig);
        }
        $cache->setValue(null, $origCache);

        @unlink($file);
        @rmdir($dir);

        // ParseException was caught, config is empty, runningInTest()=true, enabled_in_testing=false => returns false
        expect($result)->toBeFalse();
    });
});

describe('Config::get() with enabled_in_testing false and missing key', function () {
    it('returns false when key is missing and enabled_in_testing is false', function () {
        withTempConfig([
            'observers' => ['enabled_in_testing' => false],
        ], function () {
            // Key doesn't exist, runningInTest() is true, enabled_in_testing is false
            expect(Config::get('nonexistent.key', 'default'))->toBeFalse();
        });
    });
});
