<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\Concerns\WithEditors;

class InstallationPayload extends Payload
{
    use WithEditors;

    public function __construct(
        public ?string $appName = null
    ) {
    }

    public function type(): string
    {
        return 'install';
    }

    public function content(): array
    {
        return [
            'name'        => $this->appName,
            'environment' => Config::getAvailableConfig(),
            'env_path'    => rtrim(strval(getcwd()), '\/') . DIRECTORY_SEPARATOR . '.env',
            'ide_list'    => $this->editors,
        ];
    }
}
