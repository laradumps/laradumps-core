<?php

namespace LaraDumps\LaraDumpsCore\Support;

use Spatie\Backtrace\Frame;

class IdeHandle
{
    public function __construct(
        public ?Frame $frame = null,
    ) {
    }

    public function make(): array
    {
        if (empty($this->frame)) {
            return [
                'path'       => 'empty',
                'class_name' => 'empty',
                'line'       => '',
            ];
        }

        $path = $this->frame->file;
        $line = strval($this->frame->lineNumber);

        if (str_contains($path, 'Laravel Kit')) {
            $path = 'Laravel Kit';
            $line = '';
        }

        if (str_contains($path, 'eval()')) {
            $path = 'Tinker';
            $line = '';
        }

        $path = str_replace(appBasePath() . DIRECTORY_SEPARATOR, '', strval($path));

        if (str_contains($path, 'resources')) {
            $path = str_replace('resources/views/', '', strval($path));
        }

        $className = explode('/', $path);
        $className = end($className);

        return [
            'path'       => $path,
            'class_name' => $className,
            'line'       => $line,
        ];
    }
}
