<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\ConvertArrayToPhpSyntax;

class DumpPayload extends Payload
{
    public function __construct(
        public mixed $dump,
        public mixed $originalContent = null,
        public ?string $variableType = null,
        private string $screen = 'home',
        private string $label = '',
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
            'original_content' => ConvertArrayToPhpSyntax::convert($this->originalContent),
            'variable_type'    => $this->variableType,
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
