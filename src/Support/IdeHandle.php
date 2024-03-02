<?php

namespace LaraDumps\LaraDumpsCore\Support;

use LaraDumps\LaraDumpsCore\Actions\Config;
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

        $realPath = $this->frame->file;
        $line     = strval($this->frame->lineNumber);

        if (str_contains($realPath, 'Laravel Kit')) {
            $realPath = 'Laravel Kit';
            $line     = '';
        }

        if (str_contains($realPath, 'eval()')) {
            $realPath = 'Tinker';
            $line     = '';
        }

        /** @var string $realPath */
        $realPath = str_replace(appBasePath() . DIRECTORY_SEPARATOR, '', strval($realPath));

        $workDir     = Config::get('app.workdir');

        /** @var null|string $projectPath */
        $projectPath = Config::get('app.project_path');

        if (!empty($projectPath)) {
            $projectPath = str_replace(strval(runningInTest() ? $realPath : appBasePath()), $projectPath, strval($realPath));
        }

        if (str_contains($realPath, 'resources')) {
            $realPath = str_replace('resources/views/', '', strval($realPath));
        }

        $className = explode('/', $realPath);
        $className = end($className);

        return [
            'workdir'      => $workDir,
            'project_path' => $projectPath ?? '',
            'real_path'    => $realPath,
            'class_name'   => $className,
            'line'         => $line,
        ];
    }
}
