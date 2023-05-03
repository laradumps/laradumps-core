<?php

namespace LaraDumps\LaraDumpsCore\Support;

use LaraDumps\LaraDumpsCore\Actions\MakeFileHandler;

class IdeHandle
{
    public function __construct(
        public array $trace = [],
    ) {
    }

    public function make(): array
    {
        $path = strval($this->trace['file'] ?? '');
        $line = strval($this->trace['line'] ?? '');

        $fileHandle = MakeFileHandler::handle($this->trace);

        if (str_contains($path, 'Laravel Kit')) {
            $fileHandle = '';
            $path       = 'Laravel Kit';
            $line       = '';
        }

        if (str_contains($path, 'eval()')) {
            $fileHandle = '';
            $path       = 'Tinker';
            $line       = '';
        }

        if (function_exists('base_path')) {
            $basePath = base_path();
        } else {
            $basePath = rtrim(strval(getcwd()), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        $path = str_replace($basePath . DIRECTORY_SEPARATOR, '', strval($path));

        if (str_contains($path, 'resources')) {
            $path = str_replace('resources/views/', '', strval($path));
        }

        $className = explode('/', $path);
        $className = end($className);

        return [
            'handler'    => $fileHandle,
            'path'       => $path,
            'class_name' => $className,
            'line'       => $line,
        ];
    }
}
