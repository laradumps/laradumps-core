<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use Spatie\Backtrace\Frame;

final class MakeFileHandler
{
    public static function handle(
        array | Frame $frame,
        string $keyHandler = 'DS_FILE_HANDLER',
        string $forceProjectPath = 'DS_PROJECT_PATH'
    ): string {
        if (empty($frame->file)) {
            return '';
        }

        if (empty($frame->lineNumber)) {
            $frame->lineNumber = 1;
        }

        $keyHandler       = !empty($_ENV[$keyHandler]) ? $_ENV[$keyHandler] : getenv($keyHandler);
        $forceProjectPath = !empty($_ENV[$forceProjectPath]) ? $_ENV[$forceProjectPath] : getenv($forceProjectPath);

        $filename = strval(basename($frame->file));

        $filepath = strstr(strval($frame->file), $filename, true);

        if (!empty($forceProjectPath)) {
            $filepath = str_replace(strval(runningInTest() ? $filepath : appBasePath()), $forceProjectPath, strval($filepath));
        }

        $filepath = self::endsWithSeparator(strval($filepath));

        $keyHandler = str_replace('{filepath}', $filepath . $filename, $keyHandler);

        /** @phpstan-ignore-next-line  */
        return strval(str_replace('{line}', $frame->lineNumber, $keyHandler));
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
