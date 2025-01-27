<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class LabelPayload extends Payload
{
    /**
     * ColorPayload constructor.
     */
    public function __construct(
        public string $label
    ) {
    }

    public function type(): string
    {
        return 'label';
    }

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
        return new Label($this->label);
    }
}
