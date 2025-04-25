<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\{Config, IdeHandle};
use Spatie\Backtrace\Frame;

abstract class Payload
{
    private string $notificationId;

    private ?string $dumpId = null;

    private ?bool $autoInvokeApp = null;

    private ?Frame $frame = null;

    private array $codeSnippet = [];

    abstract public function type(): string;

    abstract public function toScreen(): array|Screen;

    abstract public function withLabel(): array|Label;

    abstract public function content(): array;

    public function setCodeSnippet(array $codeSnippet): void
    {
        $this->codeSnippet = $codeSnippet;
    }

    public function setFrame(array|Frame $frame): void
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

    public function setDumpId(string $id): void
    {
        $this->dumpId = $id;
    }

    public function setNotificationId(string $notificationId): void
    {
        $this->notificationId = $notificationId;
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
        if (!defined('LARADUMPS_REQUEST_ID')) {
            define('LARADUMPS_REQUEST_ID', uniqid());
        }

        return [
            'id'               => $this->notificationId,
            'application_path' => $this->applicationPath(),
            'request_id'       => LARADUMPS_REQUEST_ID,
            'sf_dump_id'       => $this->dumpId,
            'type'             => $this->type(),
            $this->type()      => $this->content(),
            'ide_handle'       => $this->ideHandle(),
            'code_snippet'     => $this->codeSnippet,
            'to_screen'        => $this->toScreen(),
            'with_label'       => $this->withLabel(),
            'extra'            => $this->getExtraPayload(),
            'auto_invoke_app'  => $this->autoInvokeApp ?? boolval(Config::get('observers.auto_invoke_app')),
        ];
    }

    private function applicationPath(): string
    {
        /** @var string $path */
        $path = Config::get('app.project_path', '');

        return $path;
    }

    private function getExtraPayload(): array
    {
        if (!class_exists(\Illuminate\Support\Facades\Context::class)) {
            return [];
        }

        /** @var bool $enabled */
        $enabled = Config::get('extra.context', true);

        if (!$enabled) {
            return [
                'context' => [],
            ];
        }

        return [
            'context' => \Illuminate\Support\Facades\Context::all(),
        ];
    }
}
