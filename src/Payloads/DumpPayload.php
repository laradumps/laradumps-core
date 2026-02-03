<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class DumpPayload extends Payload
{
    public function __construct(
        public mixed $dump,
        public mixed $originalContent = null,
        public ?string $variableType = null,
        private string $screen = 'home',
        private string $label = '',
    ) {
        $this->setOriginalContent($this->originalContent);
    }

    public function type(): string
    {
        return 'dump';
    }

    public function content(): array
    {
        return [
            'dump'          => $this->dump,
            'variable_type' => $this->variableType,
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
