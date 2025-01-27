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

    public function toScreen(): array|Screen
    {
        return [];
    }

    public function withLabel(): array|Label
    {
        return [];
    }
}
