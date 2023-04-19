<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Support\Dumper;

class TableV2Payload extends Payload
{
    public function __construct(
        protected array $values,
        protected string $headerStyle = '',
        protected string $label = 'Table',
    ) {
    }

    public function type(): string
    {
        return 'table_v2';
    }

    public function content(): array
    {
        $values = array_map(function ($value) {
            return Dumper::dump($value);
        }, $this->values);

        return [
            'values'      => $values,
            'headerStyle' => $this->headerStyle,
            'label'       => $this->label,
        ];
    }
}
