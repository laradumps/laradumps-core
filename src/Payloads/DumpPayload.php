<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class DumpPayload extends Payload
{
    public function __construct(
        public mixed $dump,
        public mixed $originalContent = null,
        public ?string $variableType = null,
    ) {
    }

    public function type(): string
    {
        return 'dump';
    }

    public function content(): array
    {
        return [
            'dump'             => $this->dump,
            'original_content' => $this->originalContent,
            'variable_type'    => $this->variableType,
        ];
    }
}
