<?php

namespace LaraDumps\LaraDumpsCore\Dispatcher;

use LaraDumps\LaraDumpsCore\Actions\Config;

class Dispatcher
{
    public function handle(array $payload): bool
    {
        // $config = Config::get('app.dispatcher', 'curl');

        $dispatcher = new Curl();

        return $dispatcher->handle($payload);
    }
}
