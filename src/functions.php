<?php

use LaraDumps\LaraDumps\LaraDumps as LaravelLaraDumps;
use LaraDumps\LaraDumpsCore\LaraDumps;
use Ramsey\Uuid\Uuid;

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
        $sendRequest = function ($args, LaraDumps $laradumps) {
            if ($args) {
                foreach ($args as $arg) {
                    $laradumps->write($arg);
                }
            }
        };

        if (class_exists(LaravelLaraDumps::class) && function_exists('app')) {
            $laradumps = app(LaravelLaraDumps::class);

            $sendRequest($args, $laradumps);

            return $laradumps;
        }

        $laradumps = new LaraDumps(
            notificationId: Uuid::uuid4()->toString(),
        );

        $sendRequest($args, $laradumps);

        return $laradumps;
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
        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId);

        foreach ($args as $arg) {
            $dump->write($arg);
        }

        die();
    }
}

if (!function_exists('ds1')) {
    function ds1(mixed ...$args): LaraDumps
    {
        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 1');
            }
        }

        return new LaraDumps($notificationId);
    }
}

if (!function_exists('ds2')) {
    function ds2(mixed ...$args): LaraDumps
    {
        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 2');
            }
        }

        return new LaraDumps($notificationId);
    }
}

if (!function_exists('ds3')) {
    function ds3(mixed ...$args): LaraDumps
    {
        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 3');
            }
        }

        return new LaraDumps($notificationId);
    }
}

if (!function_exists('ds4')) {
    function ds4(mixed ...$args): LaraDumps
    {
        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 4');
            }
        }

        return new LaraDumps($notificationId);
    }
}

if (!function_exists('ds5')) {
    function ds5(mixed ...$args): LaraDumps
    {
        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg)->toScreen('screen 5');
            }
        }

        return new LaraDumps($notificationId);
    }
}

if (!function_exists('dsq')) {
    function dsq(mixed ...$args): void
    {
        $notificationId = Uuid::uuid4()->toString();
        $dump           = new LaraDumps($notificationId);

        if ($args) {
            foreach ($args as $arg) {
                $dump->write($arg, autoInvokeApp: false);
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
