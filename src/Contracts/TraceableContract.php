<?php

namespace LaraDumps\LaraDumpsCore\Contracts;

interface TraceableContract
{
    public function setFrame(array $trace): array;
}
