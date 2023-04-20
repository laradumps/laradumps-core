<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class ScreenPayload extends Payload
{
    public function __construct(
        public string $name,
        public int $raiseIn = 0,
    ) {
    }

    public function type(): string
    {
        return 'screen';
    }

    /** @return array<string|mixed> */
    public function content(): array
    {
        return [
            'screen_name' => $this->name,
            'raise_in'    => $this->raiseIn,
        ];
    }
}
