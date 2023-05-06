<?php

namespace LaraDumps\LaraDumpsCore\Actions;

// Based on https://stackoverflow.com/questions/32307426/how-to-change-variables-in-the-env-file-dynamically-in-laravel
final class WriteEnv
{
    /**
     * @throws \Exception
     */
    public static function handle(array $settings, string $filePath = ''): void
    {
        $fileContent = '';

        if (empty($filePath)) {
            $filePath = appBasePath() . '.env';
        }

        if (file_exists($filePath)) {
            $fileContent = file_get_contents($filePath);
        }

        foreach ($settings as $key => $value) {
            //Store the key
            $original = [];

            if (!preg_match('/^[0-9a-zA-Z_]+$/i', $key)) {
                throw new \Exception("Error: '{$key}' is not a valid .env key.");
            }

            $key = strtoupper($key);

            //Wrap strings
            if ((bool) preg_match('/^\d+$/', strval(str_replace('.', '', $value))) === false
                && in_array($value, ['true', 'false']) === false
                && $value != '') {
                $value = "\"{$value}\"";
            }

            //Deal with boolean
            if (in_array($value, ['true', 'false'])) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            }

            if (preg_match("/^$key\=.*$/m", strval($fileContent), $original)) {
                //Update
                $fileContent = preg_replace("/^$key\=.*$/m", "$key=$value", strval($fileContent));
            } else {
                //Append the key to the end of file
                $fileContent .= PHP_EOL . "$key=$value";
            }

            file_put_contents($filePath, strval($fileContent));
        }
    }

    /**
     * @throws \Exception
     */
    public static function commentOldEnvKeys(string $filePath, array $keysToComment): void
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Error: file '$filePath' not found.");
        }

        if (empty($filePath)) {
            $filePath = appBasePath() . '.env';
        }

        $fileContent = strval(file_get_contents($filePath));

        foreach ($keysToComment as $key) {
            if (!preg_match('/^[0-9a-zA-Z_]+$/i', $key)) {
                throw new \Exception("Error: '$key' is not a valid .env key.");
            }

            if (!is_null($fileContent)) {
                $key = strtoupper($key);

                $fileContent = strval(preg_replace("/^$key\=.*$/m", '#$0 // laradumps v1', $fileContent));

                $fileContent = preg_replace("/^\h*$key\h*=\h*\R/m", '#$0 // laradumps v1', $fileContent);
            }
        }

        file_put_contents($filePath, $fileContent);
    }
}
