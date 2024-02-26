<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use LaraDumps\LaraDumpsCore\Payloads\Payload;
use Ramsey\Uuid\Uuid;

final class SendPayload
{
    private string $host = '127.0.0.1';

    private string $port = '9191';

    protected string $resource = '/api/dumps';

    public function __construct(private ?string $appUrl = null)
    {
        $this->appUrl ??= $this->getAppUrl();
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
        $primaryResponse = $this->sendRequest(payload: $payload);

        if (!$primaryResponse) {
            $secondaryUrl = Config::get('app.secondary_host') . ':' . Config::get('app.port') . $this->resource;

            return $this->sendRequest($secondaryUrl, $payload);
        }

        return false;
    }

    /**
     * Sends a cURL request and returns true if successful, false otherwise.
     */
    private function sendRequest(?string $url = null, array|Payload $payload = []): bool
    {
        if (is_null($url)) {
            $url = $this->appUrl;
        }

        $curlRequest = curl_init();

        curl_setopt_array($curlRequest, [
            CURLOPT_POST              => true,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_FOLLOWLOCATION    => true,
            CURLOPT_HTTPHEADER        => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS        => json_encode($payload),
            CURLOPT_URL               => $url,
            CURLOPT_TIMEOUT           => 1,
            CURLOPT_CONNECTTIMEOUT_MS => 100,
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

    private function getAppUrl(): string
    {
        return Config::get('app.primary_host', $this->host) . ':' . Config::get('app.port', $this->port) . $this->resource;
    }
}
