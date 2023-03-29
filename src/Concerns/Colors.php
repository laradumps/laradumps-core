<?php

namespace LaraDumps\LaraDumpsCore\Concerns;

use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\LaraDumps;

trait Colors
{
    public function danger(): LaraDumps
    {
        if (Config::get('send_color_in_screen')) {
            return $this->toScreen('danger');
        }

        return $this->color('red');
    }

    public function dark(): LaraDumps
    {
        return $this->color('black');
    }

    public function warning(): LaraDumps
    {
        if (boolval(Config::get('send_color_in_screen'))) {
            return $this->toScreen('warning');
        }

        return $this->color('orange');
    }

    public function success(): LaraDumps
    {
        if (boolval(Config::get('send_color_in_screen'))) {
            return $this->toScreen('success');
        }

        return $this->color('green');
    }

    public function info(): LaraDumps
    {
        if (boolval(Config::get('send_color_in_screen'))) {
            return $this->toScreen('info');
        }

        return $this->color('blue');
    }
}
