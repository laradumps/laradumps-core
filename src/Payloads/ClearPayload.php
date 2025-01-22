<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class ClearPayload extends Payload
{
    public function type(): string
    {
        return 'clear';
    }

    /** @return array<string> */
    public function content(): array
    {
        return [];
    }

    public function screen(): array|Screen
    {
        return [];
    }

    public function label(): array|Label
    {
        return [];
    }
}
