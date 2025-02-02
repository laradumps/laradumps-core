<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class Screen
{
    public function __construct(
        public string $screen_name = 'home',
        public int $raise_in = 0,
        public bool $new_window = false,
    ) {
    }
}
