<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\Config;

class InstallationPayload extends Payload
{
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
            'env_path'    => appBasePath() . '.env',
        ];
    }
}
