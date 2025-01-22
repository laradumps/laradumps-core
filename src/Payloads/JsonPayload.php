<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class JsonPayload extends Payload
{
    public function __construct(
        public string $string,
    ) {
    }

    public function type(): string
    {
        return 'json';
    }

    public function content(): array
    {
        return [
            'string'           => $this->string,
            'original_content' => $this->string,
        ];
    }

    public function screen(): array|Screen
    {
        return new Screen('screen 1');
    }

    public function label(): array|Label
    {
        return[];
    }
}
