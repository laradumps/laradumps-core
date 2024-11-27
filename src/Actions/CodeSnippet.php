<?php

namespace LaraDumps\LaraDumpsCore\Actions;

class CodeSnippet
{
    public function __construct(
        public int $linesAbove = 10,
        public int $linesBelow = 3,
        public ?int $traceLimit = null
    ) {
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
                'snippet' => is_readable($file) ? $this->fromFileAndLine($file, $line) : 'File not found or not readable.',
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

            $traceContexts[] = [
                'file'    => $traceFile,
                'line'    => $traceLine,
                'snippet' => is_readable($traceFile) ? $this->fromFileAndLine($traceFile, $traceLine) : 'File not found or not readable.',
            ];
        }

        return $traceContexts;
    }

    public function fromFileAndLine(string $file, int $line): array
    {
        $lines     = file($file, FILE_IGNORE_NEW_LINES);
        $startLine = max(1, $line - $this->linesAbove);
        $endLine   = min(count($lines), $line + $this->linesBelow); // @phpstan-ignore-line

        return array_combine(
            range($startLine, $endLine),
            array_slice($lines, $startLine - 1, $endLine - $startLine + 1) // @phpstan-ignore-line
        );
    }
}
