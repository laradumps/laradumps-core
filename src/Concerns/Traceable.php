<?php

namespace LaraDumps\LaraDumpsCore\Concerns;

trait Traceable
{
    public array $trace = [];

    protected array $backtraceExcludePaths = [
        '/vendor/laravel/framework/src/Illuminate',
        '/vendor/barryvdh',
        '/vendor/symfony',
        '/artisan',
        '/vendor/livewire',
        '/packages/laradumps',
        '/packages/laradumps-core',
        '/vendor/laradumps',
        '/vendor/laradumps-core',
    ];

    public function setTrace(array $trace): array
    {
        return $this->trace = $trace;
    }

    protected function findSource(): array
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 100);

        $sources = [];

        foreach ($stack as $trace) {
            $sources[] = $this->parseTrace($trace);
        }

        return array_filter($sources);
    }

    protected function parseTrace(array $trace): array
    {
        if (isset($trace['class']) && isset($trace['file'])) {
            return !$this->fileIsInExcludedPath($trace['file']) ? $trace : [];
        }

        return [];
    }

    protected function fileIsInExcludedPath(string $file): bool
    {
        $normalizedPath = str_replace('\\', '/', $file);

        foreach ($this->backtraceExcludePaths as $excludedPath) {
            if (str_contains($normalizedPath, $excludedPath)) {
                return true;
            }
        }

        return false;
    }
}
