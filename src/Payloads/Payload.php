<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\Concerns\Traceable;
use LaraDumps\LaraDumpsCore\Support\IdeHandle;

abstract class Payload
{
    use Traceable;

    private string $notificationId;

    private ?string $dumpId = null;

    private ?bool $autoInvokeApp = null;

    abstract public function type(): string;

    public function dumpId(string $id): void
    {
        $this->dumpId = $id;
    }

    public function notificationId(string $notificationId): void
    {
        $this->notificationId = $notificationId;
    }

    public function content(): array
    {
        return [];
    }

    public function ideHandle(): array
    {
        $ideHandle = new IdeHandle(trace: $this->trace);

        return $ideHandle->make();
    }

    public function customHandle(): array
    {
        return [];
    }

    public function autoInvokeApp(?bool $enable = null): void
    {
        $this->autoInvokeApp = $enable;
    }

    public function toArray(): array
    {
        $ideHandle = $this->ideHandle();

        if (!empty($this->customHandle())) {
            $ideHandle = $this->customHandle();
        }

        $requestId = uniqid();

        if (defined('LARADUMPS_REQUEST_ID')) {
            $requestId = defined('LARADUMPS_REQUEST_ID');
        }

        $dateTime = date('H:i:s');

        if (function_exists('now')) {
            $dateTime = now()->format('H:i:s');
        };

        return [
            'id'         => $this->notificationId,
            'request_id' => $requestId,
            'dumpId'     => $this->dumpId,
            'type'       => $this->type(),
            'meta'       => [
                'laradumps_version' => $this->getInstalledVersion(),
                'auto_invoke_app'   => $this->autoInvokeApp ?? boolval(Config::get('auto_invoke_app')),
            ],
            'content'   => $this->content(),
            'ideHandle' => $ideHandle,
            'dateTime'  => $dateTime,
        ];
    }

    public function getInstalledVersion(): ?string
    {
        if (class_exists(\Composer\InstalledVersions::class)) {
            try {
                return \Composer\InstalledVersions::getVersion('laradumps/laradumps-core');
            } catch (\Exception) {
                return '0.0.0';
            }
        }

        return '0.0.0';
    }
}
