<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Config
{
    public static function publish(string $pwd, string $filepath): bool
    {
        try {
            /** @var array $fileContent */
            $fileContent = Yaml::parseFile($filepath);

            $fileContent['app']['project_path'] = $pwd;

            $yamlContent = Yaml::dump($fileContent);

            file_put_contents($pwd . 'laradumps.yaml', $yamlContent);

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public static function get(string $key, string | bool | null $default = null): string | bool | null
    {
        try {
            /** @var array $content */
            $content = Yaml::parseFile(appBasePath() . 'laradumps.yaml');

            /** @var array $keys */
            $keys = explode('.', $key);

            foreach ($keys as $key) {
                if (!isset($content[$key])) {
                    return $default;
                }

                $content = $content[$key];
            }

            return $content;
        } catch (ParseException) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $filePath = appBasePath() . 'laradumps.yaml';

        $fileContent = file_exists($filePath) ? Yaml::parseFile($filePath) : [];

        $keys = explode('.', $key);
        /** @var array $currentArray */
        $currentArray = &$fileContent;

        foreach ($keys as $key) {
            if (!isset($currentArray[$key])) {
                $currentArray[$key] = [];
            }
            $currentArray = &$currentArray[$key];
        }

        $currentArray = $value;

        $yamlContent = Yaml::dump($fileContent);

        file_put_contents($filePath, $yamlContent);
    }

    public static function exists(): bool
    {
        return file_exists(appBasePath() . 'laradumps.yaml');
    }
}
