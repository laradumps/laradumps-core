<?php

use LaraDumps\LaraDumps\LaraDumps as LaravelLaraDumps;
use LaraDumps\LaraDumpsCore\LaraDumps;

if (!function_exists('appBasePath')) {
    function appBasePath(): string
    {
        $currentWorkingDirectory = getcwd();
        if (isset($_SERVER['LARAVEL_OCTANE']) && intval($_SERVER['LARAVEL_OCTANE']) === 1) {
            // Use base_path to get the actual project path, as octane is running inside its vendor folder.
            // We can use globally registered laravel functions when octane was detected.
            $currentWorkingDirectory = base_path();
        }

        $basePath = rtrim(strval($currentWorkingDirectory), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        foreach (['public', 'pub', 'wp-admin'] as $dir) {
            if (str_ends_with($basePath, $dir . DIRECTORY_SEPARATOR)) {
                $basePath = substr($basePath, 0, -strlen($dir . DIRECTORY_SEPARATOR));

                break;
            }
        }

        return $basePath;
    }
}

if (!function_exists('ds')) {
    function ds(mixed ...$args): LaraDumps|LaravelLaraDumps
    {
        $sendRequest = function ($args, LaraDumps $instance) {
            if ($args) {
                foreach ($args as $arg) {
                    $instance->write($arg);
                }
            }
        };

        if (class_exists(LaravelLaraDumps::class) && function_exists('app')) {
            $instance = app(LaravelLaraDumps::class);

            $sendRequest($args, $instance);

            return $instance;
        }

        $instance = new LaraDumps();

        $sendRequest($args, $instance);

        return $instance;
    }
}

if (!function_exists('phpinfo')) {
    function phpinfo(): LaraDumps
    {
        return ds()->phpinfo();
    }
}

if (!function_exists('dsd')) {
    function dsd(mixed ...$args): void
    {
        $instance = new LaraDumps();

        foreach ($args as $arg) {
            $instance->write($arg);
        }

        die();
    }
}

if (!function_exists('dsq')) {
    function dsq(mixed ...$args): void
    {
        $instance = new LaraDumps();

        if ($args) {
            foreach ($args as $arg) {
                $instance->write($arg, autoInvokeApp: false);
            }
        }
    }
}

if (!function_exists('runningInTest')) {
    function runningInTest(): bool
    {
        if (PHP_SAPI != 'cli') {
            return false;
        }

        if (str_contains($_SERVER['argv'][0], 'phpunit')) {
            return true;
        }

        if (str_contains($_SERVER['argv'][0], 'pest')) {
            return true;
        }

        return false;
    }
}
