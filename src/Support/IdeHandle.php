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
        $workDir = Config::get('app.workdir');

        /** @var null|string $projectPath */
        $projectPath = Config::get('app.project_path');

        if (empty($this->frame)) {
            return [
                'path'         => 'empty',
                'class_name'   => 'empty',
                'real_path'    => '',
                'project_path' => $projectPath,
                'line'         => '',
                'separator'    => DIRECTORY_SEPARATOR,
            ];
        }

        $realPath = $this->frame->file;
        $line     = strval($this->frame->lineNumber);

        /** @var string $realPath */
        $realPath = str_replace(appBasePath() . DIRECTORY_SEPARATOR, '', strval($realPath));

        $className = explode(DIRECTORY_SEPARATOR, $realPath);
        $className = end($className);

        return [
            'workdir'      => $workDir,
            'project_path' => $projectPath ?? '',
            'real_path'    => $realPath,
            'class_name'   => $className,
            'line'         => $line,
            'separator'    => DIRECTORY_SEPARATOR,
        ];
    }
}
