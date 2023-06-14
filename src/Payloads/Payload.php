<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\Concerns\Traceable;
use LaraDumps\LaraDumpsCore\Support\IdeHandle;

abstract class Payload
{
    use Traceable;

    private bool $dispatched = false;

    private string $notificationId;

    private ?string $dumpId = null;

    private ?bool $autoInvokeApp = null;

    abstract public function type(): string;

    public function setDispatch(bool $dispatched): void
    {
        $this->dispatched = $dispatched;
    }

    public function getDispatch(): bool
    {
        return $this->dispatched;
    }

    public function setDumpId(string $id): void
    {
        $this->dumpId = $id;
    }

    public function setNotificationId(string $notificationId): void
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

        if (!defined('LARADUMPS_REQUEST_ID')) {
            define('LARADUMPS_REQUEST_ID', uniqid());
        }

        $dateTime = date('H:i:s');

        if (function_exists('now')) {
            $dateTime = now()->format('H:i:s');
        };

        return [
            'id'         => $this->notificationId,
            'request_id' => LARADUMPS_REQUEST_ID,
            'sf_dump_id' => $this->dumpId,
            'type'       => $this->type(),
            'meta'       => [
                'laradumps_version' => $this->getInstalledVersion(),
                'auto_invoke_app'   => $this->autoInvokeApp ?? boolval(Config::get('auto_invoke_app')),
            ],
            $this->type() => $this->content(),
            'ide_handle'  => $ideHandle,
            'date_time'   => $dateTime,
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
