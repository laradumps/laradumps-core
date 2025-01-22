<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\Dumper;

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

    public function screen(): array|Screen
    {
        return new Screen('screen 1');
    }

    public function label(): array|Label
    {
        return new Label();
    }
}
