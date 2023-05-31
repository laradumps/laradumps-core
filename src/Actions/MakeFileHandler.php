<?php

namespace LaraDumps\LaraDumpsCore\Actions;

final class MakeFileHandler
{
    public static function handle(
        array $trace,
        string $keyHandler = 'DS_FILE_HANDLER',
        string $forceProjectPath = 'DS_PROJECT_PATH'
    ): string {
        if (empty($trace) || empty($trace['file'])) {
            return '';
        }

        if (empty($trace['line'])) {
            $trace['line'] = 1;
        }

        $keyHandler       = !empty($_ENV[$keyHandler]) ? $_ENV[$keyHandler] : getenv($keyHandler);
        $forceProjectPath = !empty($_ENV[$forceProjectPath]) ? $_ENV[$forceProjectPath] : getenv($forceProjectPath);

        $filename = strval(basename($trace['file']));

        $filepath = strstr(strval($trace['file']), $filename, true);

        if (!empty($forceProjectPath)) {
            $filepath = str_replace(strval(runningInTest() ? $filepath : appBasePath()), $forceProjectPath, strval($filepath));
        }

        $filepath = self::endsWithSeparator(strval($filepath));

        $keyHandler = str_replace('{filepath}', $filepath . $filename, $keyHandler);

        /** @phpstan-ignore-next-line  */
        return strval(str_replace('{line}', $trace['line'], $keyHandler));
    }

    protected static function endsWithSeparator(string $filepath): string
    {
        $separator = '';

        if (str_contains($filepath, '/')) {
            $separator = '/';
        }

        if (str_contains($filepath, '\\')) {
            $separator = '\\';
        }

        if (substr($filepath, -1) !== $separator) {
            $filepath .= $separator;
        }

        return $filepath;
    }
}
