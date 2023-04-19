<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class ValidJsonPayload extends Payload
{
    public function type(): string
    {
        return 'json_validate';
    }
}
