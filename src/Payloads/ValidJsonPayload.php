<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class ValidJsonPayload extends Payload
{
    public function type(): string
    {
        return 'json_validate';
    }

    public function screen(): array|Screen
    {
        return [];
    }

    public function label(): array|Label
    {
        return [];
    }

    public function content(): array
    {
        return [];
    }
}
