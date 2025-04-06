<?php

namespace LaraDumps\LaraDumpsCore\Dispatcher;

use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use Ramsey\Uuid\Uuid;

class Curl implements PayloadSenderInterface
{
    private const DEFAULT_HOST       = '127.0.0.1';
    private const DEFAULT_PORT       = '9191';
    private const RESOURCE           = '/api/dumps';
    private const TIMEOUT            = 1;
    private const CONNECT_TIMEOUT_MS = 100;

    private static bool $ignorePrimary = false;

    private ?string $primaryUrl = null;

    private ?string $secondaryUrl = null;

    public static function make(): self
    {
        return new self();
    }

    public function handle(array|Payload $payload): bool
    {
        /** @var string $jsonPayload */
        $jsonPayload = json_encode($payload);

        if (!self::$ignorePrimary) {
            if ($this->dispatch($this->getPrimaryUrl(), $jsonPayload)) {
                return true;
            }
        }

        $success = $this->dispatch($this->getSecondaryUrl(), $jsonPayload);

        if ($success && !self::$ignorePrimary) {
            self::$ignorePrimary = true;
        }

        return $success;
    }

    public function dispatch(string $url, string $payloadString): bool
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL               => $url,
            CURLOPT_POST              => true,
            CURLOPT_POSTFIELDS        => $payloadString,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_HTTPHEADER        => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT           => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT_MS => self::CONNECT_TIMEOUT_MS,
        ]);

        /** @var string $response */
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return false;
        }

        /** @var object $result */
        $result = json_decode($response);

        return isset($result->id) && Uuid::isValid($result->id);
    }

    private function getPrimaryUrl(): string
    {
        return $this->primaryUrl ??= Config::get('app.primary_host', self::DEFAULT_HOST) . ':' .
            Config::get('app.port', self::DEFAULT_PORT) . self::RESOURCE;
    }

    private function getSecondaryUrl(): string
    {
        return $this->secondaryUrl ??= Config::get('app.secondary_host') . ':' .
            Config::get('app.port') . self::RESOURCE;
    }
}
