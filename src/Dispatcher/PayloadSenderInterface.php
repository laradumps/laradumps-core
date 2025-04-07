<?php

namespace LaraDumps\LaraDumpsCore\Dispatcher;

use LaraDumps\LaraDumpsCore\Payloads\Payload;

interface PayloadSenderInterface
{
    public function handle(array|Payload $payload): bool;
}
