<?php

namespace LaraDumps\LaraDumpsCore\Support;

class CheckCodeSnippet
{
    public string $contents;

    public string $file;

    public int $line;

    public string $realPath;

    public int $startLine;

    public function __construct(\splFileInfo $file, int $line)
    {
        $realPath = isset($_ENV['IGNITION_LOCAL_SITES_PATH'])
            ? $_ENV['IGNITION_LOCAL_SITES_PATH'] . DIRECTORY_SEPARATOR . str_replace(appBasePath(), '', $file->getRealPath())
            : $file->getRealPath();

        $this->line      = $line + 1;
        $this->startLine = max(0, $line - 1);
        $this->file      = $file->getRealPath();
        $this->realPath  = 'file:///' . $realPath;
        $this->contents  = $this->renderLines($line);
    }

    private function renderLines(int $line): string
    {
        $contents = '';

        if ($fileContents = file($this->file)) {
            foreach ([$line - 2, $line - 1, $line, $line + 1, $line + 2] as $value) {
                if (isset($fileContents[$value])) {
                    $contents .= htmlspecialchars(rtrim($fileContents[$value], "\n\r")) . PHP_EOL;
                }
            }
        }

        return $contents;
    }
}
