<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\Table;

class TablePayload extends Payload
{
    public function __construct(
        private mixed $data = [],
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
        return (new Table($this->data, $this->name))->make();
    }
}
