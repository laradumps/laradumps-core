<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\Table;

class TablePayload extends Payload
{
    public function __construct(
        private iterable|object $data = [],
        private string $name = '',
        protected string $screen = 'home',
        protected string $label = 'Table',
    ) {
        if (empty($this->name)) {
            $this->name = 'Table';
        }
    }

    public function type(): string
    {
        return 'table';
    }

    public function content(): array
    {
        return Table::make($this->data, $this->name);
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
