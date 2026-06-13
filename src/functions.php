<?php

use LaraDumps\LaraDumps\LaraDumps as LaravelLaraDumps;
use LaraDumps\LaraDumpsCore\Actions\{Config, VariableParser};
use LaraDumps\LaraDumpsCore\LaraDumps;

if (!function_exists('appBasePath')) {
    function appBasePath(): string
    {
        $pwd = (defined('LARAVEL_START') || isset($_SERVER['LARAVEL_OCTANE'])) && function_exists('app')
            ? app()->basePath() // @codeCoverageIgnore
            : (getcwd() ?: '');

        if ($pwd === '') {
            return DIRECTORY_SEPARATOR;
        }

        $basePath = rtrim(realpath($pwd) ?: $pwd, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        foreach (['public', 'pub', 'wp-admin', 'web'] as $dir) {
            $suffix = DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;

            if (str_ends_with($basePath, $suffix)) {
                return substr($basePath, 0, -strlen($suffix)) . DIRECTORY_SEPARATOR;
            }
        }

        return $basePath;
    }
}

if (!function_exists('ds')) {
    function ds(mixed ...$args): LaraDumps|LaravelLaraDumps
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $sendRequest = function ($args, LaraDumps $instance) use ($trace) {
            if (!$args) {
                return;
            }

            if (Config::get('config.grouped_dumps', false) && count($args) > 1) {
                $instance->writeGrouped($args, $trace['file'], $trace['line']);

                return;
            }

            $varInfos = VariableParser::parse($trace['file'], $trace['line'], count($args));

            foreach ($args as $i => $arg) {
                $varName = $varInfos[$i]['name'] ?? null;
                $instance->write($arg, variableName: $varName);
            }
        };

        if (class_exists(LaravelLaraDumps::class) && function_exists('app')) {
            // @codeCoverageIgnoreStart
            $instance = app(LaravelLaraDumps::class);

            $sendRequest($args, $instance);

            return $instance;
            // @codeCoverageIgnoreEnd
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
    /**
     * @codeCoverageIgnore
     */
    function dsd(mixed ...$args): void
    {
        $trace    = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $instance = new LaraDumps();

        if (Config::get('config.grouped_dumps', false) && count($args) > 1) {
            $instance->writeGrouped($args, $trace['file'], $trace['line']);
        } else {
            $varInfos = VariableParser::parse($trace['file'], $trace['line'], count($args));

            foreach ($args as $i => $arg) {
                $instance->write($arg, variableName: $varInfos[$i]['name'] ?? null);
            }
        }

        die();
    }
}

if (!function_exists('dsq')) {
    function dsq(mixed ...$args): void
    {
        $trace    = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $instance = new LaraDumps();

        if (!$args) {
            return;
        }

        if (Config::get('config.grouped_dumps', false) && count($args) > 1) {
            $instance->writeGrouped($args, $trace['file'], $trace['line']);

            return;
        }

        $varInfos = VariableParser::parse($trace['file'], $trace['line'], count($args));

        foreach ($args as $i => $arg) {
            $instance->write($arg, autoInvokeApp: false, variableName: $varInfos[$i]['name'] ?? null);
        }
    }
}

if (!function_exists('runningInTest')) {
    function runningInTest(): bool
    {
        if (PHP_SAPI != 'cli') {
            return false; // @codeCoverageIgnore
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
