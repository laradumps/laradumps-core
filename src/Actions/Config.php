<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Config
{
    public static function get(string $key, string | bool | null $default = null): string | bool | null
    {
        $file = appBasePath() . 'laradumps.yaml';

        try {
            $content = Yaml::parseFile($file);

            $keys = explode('.', $key);

            foreach ($keys as $key) {
                if (!isset($content[$key])) {
                    return null;
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
}
