<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class ScreenPayload extends Payload
{
    public function __construct(
        public string $name,
        public int $raiseIn = 0,
        public bool $newWindow = false
    ) {
    }

    public function type(): string
    {
        return 'screen';
    }

    public function content(): array
    {
        return [];
    }

    public function toScreen(): array|Screen
    {
        return new Screen($this->name, $this->raiseIn, $this->newWindow);
    }

    public function withLabel(): array|Label
    {
        return [];
    }
}
