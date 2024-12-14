<?php

namespace LaraDumps\LaraDumpsCore\Support;

use LaraDumps\LaraDumpsCore\Actions\Config;

class CodeSnippet
{
    public function __construct(
        public ?int $linesAbove = null,
        public ?int $linesBelow = null,
        public ?int $traceLimit = null
    ) {
        $this->linesAbove ??= Config::get('code_snippet.above', 7); //@phpstan-ignore-line
        $this->linesBelow ??= Config::get('code_snippet.below', 3); //@phpstan-ignore-line
    }

    public function fromException(\Throwable $exception): array
    {
        return $this->getCodeSnippetFromTrace($exception->getTrace(), $exception->getFile(), $exception->getLine());
    }

    public function fromDebugBacktrace(array $backtrace): array
    {
        return $this->getCodeSnippetFromTrace($backtrace);
    }

    private function getCodeSnippetFromTrace(array $trace, ?string $file = null, ?int $line = null): array
    {
        $traceContexts = [];

        if ($file !== null && $line !== null) {
            $traceContexts[] = [
                'file'    => $file,
                'line'    => $line,
                'snippet' => is_readable($file) ? $this->fromFileAndLine($file, $line) : [$line, 'File not found or not readable.'],
                'route'   => null,
            ];
        }

        $trace = $this->traceLimit !== null
            ? array_slice($trace, 0, $this->traceLimit)
            : $trace;

        foreach ($trace as $traceItem) {
            if (!isset($traceItem['file'], $traceItem['line'])) {
                continue;
            }

            $traceFile = $traceItem['file'];
            $traceLine = $traceItem['line'];

            $route = $traceItem['function'];

            if (isset($traceItem['class'])) {
                $route = $traceItem['class'] . ':' . $traceItem['function'];
            }

            $traceContexts[] = [
                'file'    => $traceFile,
                'route'   => $route,
                'line'    => $traceLine,
                'snippet' => is_readable($traceFile) ? $this->fromFileAndLine($traceFile, $traceLine) : [$line, 'File not found or not readable.'],
            ];
        }

        $reorganizedContexts = [];

        $firstItem = $traceContexts[0];

        for ($i = 1; $i < count($traceContexts); $i++) {
            $firstItem['route']    = $traceContexts[$i]['route'];
            $reorganizedContexts[] = $firstItem;

            $firstItem = $traceContexts[$i];
        }

        $reorganizedContexts[] = $firstItem;

        return $reorganizedContexts;
    }

    public function fromFileAndLine(string $file, int $line): array
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES);

        if ($lines === false || empty($lines)) {
            return [];
        }

        $startLine = max(1, $line - $this->linesAbove);
        $endLine   = min(count($lines), $line + $this->linesBelow);

        $extractedLines = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);

        $keys = range($startLine, $endLine);

        if (count($keys) !== count($extractedLines)) {
            return [];
        }

        return array_combine($keys, $extractedLines);
    }
}
