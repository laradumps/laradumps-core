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
        return $this->getContextFromTrace($exception->getTrace(), $exception->getFile(), $exception->getLine());
    }

    public function fromDebugBacktrace(array $backtrace): array
    {
        return $this->getContextFromTrace($backtrace);
    }

    private function getContextFromTrace(array $trace, ?string $file = null, ?int $line = null): array
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

            $filePath   = $traceItem['file'];
            $targetLine = $traceItem['line'];

            $traceContexts[] = [
                'file'    => $filePath,
                'line'    => $targetLine,
                'snippet' => is_readable($filePath) ? $this->fromFileAndLine($filePath, $targetLine) : 'File not found or not readable.',
            ];
        }

        return $traceContexts;
    }

    public function fromFileAndLine(string $filePath, int $targetLine): array
    {
        $lines     = file($filePath, FILE_IGNORE_NEW_LINES);
        $startLine = max(1, $targetLine - $this->linesAbove);
        $endLine   = min(count($lines), $targetLine + $this->linesBelow);

        return array_combine(
            range($startLine, $endLine),
            array_slice($lines, $startLine - 1, $endLine - $startLine + 1)
        );
    }
}
