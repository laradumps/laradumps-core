<?php

namespace LaraDumps\LaraDumpsCore\Contracts;

interface TraceableContract
{
    public function setTrace(array $trace): array;
}
