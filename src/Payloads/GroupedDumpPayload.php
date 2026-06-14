<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class GroupedDumpPayload extends Payload
{
    public function __construct(private readonly array $items)
    {
    }

    public function type(): string
    {
        return 'dump_group';
    }

    public function content(): array
    {
        return ['items' => $this->items];
    }

    public function toScreen(): array|Screen
    {
        return new Screen('home');
    }

    public function withLabel(): array|Label
    {
        return new Label('');
    }
}
