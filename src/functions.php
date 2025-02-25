<?php

use LaraDumps\LaraDumps\LaraDumps as LaravelLaraDumps;
use LaraDumps\LaraDumpsCore\LaraDumps;

if (!function_exists('appBasePath')) {
    function appBasePath(): string
    {
        if (function_exists('base_path')) {
            return base_path();
        }

        $basePath = rtrim(strval(getcwd()), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

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
