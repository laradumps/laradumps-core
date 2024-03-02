<?php

use LaraDumps\LaraDumps\LaraDumps as LaravelLaraDumps;
use LaraDumps\LaraDumpsCore\LaraDumps;

if (!function_exists('appBasePath')) {
    function appBasePath(): string
    {
        $basePath = rtrim(strval(getcwd()), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (str_contains($basePath, 'public')) {
            $basePath = str_replace('public' . DIRECTORY_SEPARATOR, '', $basePath);
        }

        return $basePath;
    }
}

if (!function_exists('ds')) {
    function ds(mixed ...$args)
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
        return isset($_SERVER['argv']) && !empty($_SERVER['argv']) ? str_contains($_SERVER['argv'][0], 'pest') : PHP_SAPI === "cli";
    }
}
