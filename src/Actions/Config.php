<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private static ?array $cachedContent = null;

    private static string $configFilePath;

    private static function init(): void
    {
        if (!isset(self::$configFilePath)) {
            self::$configFilePath = self::locateConfigFile();
        }
    }

    private static function locateConfigFile(): string
    {
        $directory = getcwd();

        if ($directory !== false) {
            $directory = rtrim(realpath($directory) ?: $directory, DIRECTORY_SEPARATOR);

            while (@is_dir($directory)) {
                $candidate = $directory . DIRECTORY_SEPARATOR . 'laradumps.yaml';

                if (file_exists($candidate)) {
                    return $candidate;
                }

                $parent = dirname($directory);

                if ($parent === $directory) {
                    break;
                }

                $directory = $parent;
            }
        }

        return appBasePath() . 'laradumps.yaml';
    }

    private static function loadConfig(): array
    {
        self::init();

        if (self::$cachedContent === null) {
            try {
                self::$cachedContent = file_exists(self::$configFilePath)
                    ? (array) Yaml::parseFile(self::$configFilePath)
                    : [];
            } catch (ParseException) {
                self::$cachedContent = [];
            }
        }

        return self::$cachedContent;
    }

    private static function saveConfig(array $content): void
    {
        self::init();
        self::$cachedContent = $content;

        $yamlContent = Yaml::dump($content, 4, 2);
        file_put_contents(self::$configFilePath, $yamlContent);
    }

    public static function sync(array $defaults): bool
    {
        $current    = self::loadConfig();
        $reconciled = self::reconcile($defaults, $current);

        if ($reconciled === $current) {
            return false;
        }

        self::saveConfig($reconciled);

        return true;
    }

    private static function reconcile(array $defaults, array $current): array
    {
        $result = [];

        foreach ($defaults as $key => $defaultValue) {
            if (array_key_exists($key, $current)) {
                $result[$key] = (is_array($defaultValue) && is_array($current[$key]))
                    ? self::reconcile($defaultValue, $current[$key])
                    : $current[$key];

                continue;
            }

            $result[$key] = $defaultValue;
        }

        return $result;
    }

    public static function publish(string $pwd, string $filepath): bool
    {
        try {
            /** @var array $fileContent */
            $fileContent                        = Yaml::parseFile($filepath);
            $fileContent['app']['project_path'] = $pwd;

            self::saveConfig($fileContent);

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $content = self::loadConfig();

        $enabledInTesting = $content['observers']['enabled_in_testing'] ?? false;

        $keys    = explode('.', $key);
        $current = $content;

        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                if (runningInTest() && !$enabledInTesting) {
                    return false;
                }

                return $default;
            }
            $current = $current[$key];
        }

        if (runningInTest() && !$enabledInTesting) {
            return false;
        }

        return $current;
    }

    public static function set(string $key, mixed $value): void
    {
        $content      = self::loadConfig();
        $keys         = explode('.', $key);
        $currentArray = &$content;

        foreach ($keys as $key) {
            if (!isset($currentArray[$key])) {
                $currentArray[$key] = [];
            }
            $currentArray = &$currentArray[$key];
        }

        $currentArray = $value;
        self::saveConfig($content);
    }

    public static function exists(): bool
    {
        self::init();

        return file_exists(self::$configFilePath);
    }
}
