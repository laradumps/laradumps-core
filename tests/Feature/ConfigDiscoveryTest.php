<?php

use LaraDumps\LaraDumpsCore\Actions\Config;

describe('Config file discovery', function () {
    beforeEach(function () {
        $this->originalCwd = getcwd();
        $this->root        = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ld_config_discovery_' . uniqid();
    });

    afterEach(function () {
        chdir($this->originalCwd);

        $cleanup = function (string $dir) use (&$cleanup): void {
            if (!is_dir($dir)) {
                return;
            }

            foreach (scandir($dir) as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $entry;
                is_dir($path) ? $cleanup($path) : @unlink($path);
            }
            @rmdir($dir);
        };
        $cleanup($this->root);
    });

    $locate = function (): string {
        // ReflectionMethod kann private Methoden ab PHP 8.1 ohne setAccessible aufrufen
        return (new ReflectionMethod(Config::class, 'locateConfigFile'))->invoke(null);
    };

    it('walks up the directory tree to find laradumps.yaml', function () use ($locate) {
        $nested = $this->root . '/public/typo3';
        mkdir($nested, 0o777, true);
        file_put_contents($this->root . '/laradumps.yaml', "app:\n  port: 9191\n");

        // Simuliert einen verschachtelten Einstiegspunkt (z.B. TYPO3-Backend)
        chdir($nested);

        expect($locate())->toBe(
            realpath($this->root) . DIRECTORY_SEPARATOR . 'laradumps.yaml'
        );
    });

    it('falls back to the document root location when no config exists', function () use ($locate) {
        $public = $this->root . '/public';
        mkdir($public, 0o777, true);

        chdir($public);

        // Keine laradumps.yaml im Baum -> Fallback via appBasePath (public/ wird abgeschnitten)
        expect($locate())->toBe(
            realpath($this->root) . DIRECTORY_SEPARATOR . 'laradumps.yaml'
        );
    });
});
