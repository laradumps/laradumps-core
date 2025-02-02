<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class JsonPayload extends Payload
{
    public function __construct(
        public string $string,
        private string $screen = 'home',
        private string $label = '',
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

    public function toScreen(): array|Screen
    {
        return new Screen($this->screen);
    }

    public function withLabel(): array|Label
    {
        return new Label($this->label);
    }
}
