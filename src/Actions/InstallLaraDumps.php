<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use LaraDumps\LaraDumpsCore\Payloads\InstallationPayload;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;

class InstallLaraDumps extends Command
{
    protected static array $hosts = [
        '127.0.0.1',
        'host.docker.internal',
        '10.211.55.2',
        '0.0.0.0',
    ];

    public static function install(): bool
    {
        $result = false;

        foreach (self::$hosts as $host) {
            $payload = new InstallationPayload();
            $payload->notificationId(Uuid::uuid4()->toString());

            $result = SendPayload::baseUrl($host)->handle($payload->toArray());

            if (boolval($result)) {
                Config::set('host', $host);

                $result = true;

                break;
            }
        }

        if (!$result) {
            return false;
        }

        return true;
    }
}
