<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class Label
{
    public function __construct(
        public string $label = 'dump',
    ) {
    }
}
