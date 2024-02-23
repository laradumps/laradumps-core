<?php

namespace LaraDumps\LaraDumpsCore\Concerns;

use Spatie\Backtrace\{Backtrace, Frame};

trait Traceable
{
    private array $backtraceExcludePaths = [
        '/vendor/laravel/framework/src/Illuminate',
        '/artisan',
        '/vendor/livewire',
        '/packages/laradumps',
        '/packages/laradumps-core',
        '/laradumps/laradumps/',
        '/laradumps/laradumps-core/',
    ];

    public function parseFrame(Backtrace $backtrace)
    {
        $frames = collect($backtrace->frames())
            ->where('applicationFrame', true)
            ->filter(function ($frame) {
                $normalizedPath = str_replace('\\', '/', $frame->file);

                foreach ($this->backtraceExcludePaths as $excludedPath) {
                    if (str_contains($normalizedPath, $excludedPath)) {
                        return false;
                    }
                }

                return true;
            })
            ->toArray();

        return $frames[array_key_first($frames)] ?? [];
    }
}
