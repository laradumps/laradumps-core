<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use LaraDumps\LaraDumpsCore\Payloads\Payload;
use Ramsey\Uuid\Uuid;

final class SendPayload
{
    protected string $port = '9191';

    protected string $resource = '/api/dumps';

    public function __construct(private ?string $appUrl = null)
    {
        $this->appUrl = ($this->appUrl ?: Config::get('host')) . ':' . $this->port . $this->resource;
    }

    public static function baseUrl(string $host): SendPayload
    {
        return new self($host);
    }

    public static function make(): SendPayload
    {
        return new self();
    }

    /**
     * Sends Payload to the Desktop App
     */
    public function handle(array|Payload $payload): bool
    {
        $curlRequest = curl_init();

        curl_setopt_array($curlRequest, [
            CURLOPT_POST              => true,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_FOLLOWLOCATION    => true,
            CURLOPT_HTTPHEADER        => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS        => json_encode($payload),
            CURLOPT_URL               => $this->appUrl,
            CURLOPT_TIMEOUT           => 1,
            CURLOPT_CONNECTTIMEOUT_MS => 10,
        ]);

        $exec = curl_exec($curlRequest);

        /** @var string $exec */
        $result = json_decode($exec);

        /** @var null|object $result */
        if (is_null($result)) {
            return false;
        }

        return Uuid::isValid($result->id ?? '');
    }
}
