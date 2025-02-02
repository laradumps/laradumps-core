<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class ColorPayload extends Payload
{
    public function __construct(
        public string $color,
        private string $screen = 'home',
        private string $label = '',
    ) {
    }

    public function type(): string
    {
        return 'color';
    }

    /** @return array<string> */
    public function content(): array
    {
        return [
            'color' => $this->color,
        ];
    }

    public function toScreen(): array|Screen
    {
        return new Screen($this->screen);
    }

    public function withLabel(): array|Label
    {
        return new Label($this->label);
    }
}
