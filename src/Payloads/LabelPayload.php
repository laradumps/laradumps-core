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
        return [
            'label' => $this->label,
        ];
    }

    public function screen(): array|Screen
    {
        return [];
    }

    public function label(): array|Label
    {
        return[];
    }
}
