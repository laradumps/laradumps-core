<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use Symfony\Component\Process\Process;

final class GitDirtyFiles
{
    /**
     * @internal
     * Code snippet taken from laravel/pint
     * url: https://github.com/laravel/pint/pull/130
     */
    public static function run(): array
    {
        $process = new Process(['git', 'status', '--short', '--', '*.php']);
        $process->run();

        if (!$process->isSuccessful()) {
            return [];
        }

        $output = $process->getOutput();
        $lines  = (array) preg_split('/\R+/', $output, -1, PREG_SPLIT_NO_EMPTY);
        $files  = [];

        foreach ($lines as $file) {
            $file     = strval($file);
            $fileName = substr($file, 3);
            $status   = substr($file, 0, 3);

            if (!empty($status) && str_ends_with($status, ' ')) {
                $status = trim($status);

                if ($status !== 'D') {
                    if ($status === 'R') {
                        $fileName = substr($fileName, strpos($fileName, ' -> ') + 4);
                    }

                    $files[] = getcwd() . '/' . $fileName;
                }
            }
        }

        return $files;
    }
}
