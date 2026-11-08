<?php

use LaraDumps\LaraDumpsCore\Actions\Config;

uses()->beforeEach(fn () => (new ReflectionProperty(Config::class, 'checkedAt'))->setValue(null, PHP_FLOAT_MAX))->in(__DIR__);

function adjustPathToDirectorySeparator(string $path): string
{
    return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}
