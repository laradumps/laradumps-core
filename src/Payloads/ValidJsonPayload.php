<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class ValidJsonPayload extends Payload
{
    public function type(): string
    {
        return 'json_validate';
    }

    public function toScreen(): array|Screen
    {
        return [];
    }

    public function withLabel(): array|Label
    {
        return [];
    }

    public function content(): array
    {
        return [];
    }
}
