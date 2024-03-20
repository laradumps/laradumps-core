<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\{Config, IdeHandle};
use Spatie\Backtrace\Frame;

abstract class Payload
{
    private bool $dispatched = false;

    private string $notificationId;

    private ?string $dumpId = null;

    private ?bool $autoInvokeApp = null;

    private ?Frame $frame = null;

    abstract public function type(): string;

    public function setDispatch(bool $dispatched): void
    {
        $this->dispatched = $dispatched;
    }

    public function setFrame(array | Frame $frame): void
    {
        if (is_array($frame)) {
            $this->frame = new Frame(
                file: $frame['file'],
                lineNumber: $frame['line'],
                arguments: null,
                method: $frame['function'] ?? null,
                class: $frame['class'] ?? null
            );

            return;
        }

        $this->frame = $frame;
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
        $ideHandle = new IdeHandle($this->frame);

        return $ideHandle->make();
    }

    public function autoInvokeApp(?bool $enable = null): void
    {
        $this->autoInvokeApp = $enable;
    }

    public function toArray(): array
    {
        $ideHandle = $this->ideHandle();

        if (!defined('LARADUMPS_REQUEST_ID')) {
            define('LARADUMPS_REQUEST_ID', uniqid());
        }

        return [
            'id'               => $this->notificationId,
            'application_path' => $this->applicationPath(),
            'request_id'       => LARADUMPS_REQUEST_ID,
            'sf_dump_id'       => $this->dumpId,
            'type'             => $this->type(),
            'meta'             => [
                'laradumps_version' => $this->getInstalledVersion(),
                'auto_invoke_app'   => $this->autoInvokeApp ?? boolval(Config::get('observers.auto_invoke_app')),
            ],
            $this->type() => $this->content(),
            'ide_handle'  => $ideHandle,
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

    private function applicationPath(): string
    {
        /** @var string $path */
        $path = Config::get('app.project_path', '');

        return $path;
    }
}
