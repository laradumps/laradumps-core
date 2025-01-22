<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\Table;

class TablePayload extends Payload
{
    public function __construct(
        private iterable|object $data = [],
        private string $name = '',
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

    public function screen(): array|Screen
    {
        return new Screen('screen 1');
    }

    public function label(): array|Label
    {
        return new Label();
    }
}
