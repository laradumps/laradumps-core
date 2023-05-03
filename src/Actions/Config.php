<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use Exception;

class Config
{
    protected static function getEnvironment(): array
    {
        return [
            'host'                      => 'DS_APP_HOST',
            'sleep'                     => 'DS_SLEEP',
            'auto_clear_on_page_reload' => 'DS_AUTO_CLEAR_ON_PAGE_RELOAD',
            'auto_invoke_app'           => 'DS_AUTO_INVOKE_APP',
            'preferred_ide'             => 'DS_PREFERRED_IDE',
            'send_color_in_screen'      => 'DS_SEND_COLOR_IN_SCREEN',
            'testing'                   => 'DS_RUNNING_IN_TESTS',
            'installed'                 => 'DS_INSTALLED',
        ];
    }

    public static function getAvailableConfig(): array
    {
        return array_values(array_filter(static::getEnvironment(), function ($key) {
            return !in_array($key, [
                'DS_APP_HOST',
                'DS_SLEEP',
                'DS_INSTALLED',
                'DS_PREFERRED_IDE',
                'DS_RUNNING_IN_TESTS',
            ]);
        }));
    }

    public static function get(string $key): mixed
    {
        $value = $_ENV[static::getEnvironment()[$key] ?? null] ?? false;

        return match ($value) {
            'true'  => true,
            'false' => false,
            default => $value,
        };
    }

    /**
     * @throws Exception
     */
    public static function set(string $key, mixed $value): void
    {
        if (!isset(static::getEnvironment()[$key])) {
            return;
        }

        $value = match ($value) {
            '0', 0 => false,
            '1', 1 => true,
            default => $value,
        };

        $_ENV[static::getEnvironment()[$key]] = $value;

        WriteEnv::handle([static::getEnvironment()[$key] => $value]);
    }
}
