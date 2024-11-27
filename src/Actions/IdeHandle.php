<?php

namespace LaraDumps\LaraDumpsCore\Actions;

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

        /** @var null|string $wslConfig */
        $wslConfig = Config::get('app.wsl_config');

        if (empty($this->frame)) {
            return [
                'path'         => 'empty',
                'class_name'   => 'empty',
                'real_path'    => '',
                'project_path' => $projectPath,
                'line'         => '',
                'separator'    => DIRECTORY_SEPARATOR,
                'wsl_config'   => $wslConfig,
            ];
        }

        $realPath = $this->frame->file;
        $line     = strval($this->frame->lineNumber);

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
            'wsl_config'   => $wslConfig,
        ];
    }
}
