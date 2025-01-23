<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class ColorPayload extends Payload
{
    public function __construct(
        public string $color,
        private string $screen = 'screen 1',
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

    public function screen(): array|Screen
    {
        return new Screen($this->screen);
    }

    public function label(): array|Label
    {
        return new Label($this->label);
    }
}
