<?php

namespace LaraDumps\LaraDumpsCore\Actions;

use LaraDumps\LaraDumpsCore\Payloads\{InstallationPayload, Payload};
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
            if (class_exists(\LaraDumps\LaraDumps\Payloads\InstallationPayload::class)) {
                $installationPayload = \LaraDumps\LaraDumps\Payloads\InstallationPayload::class;
            } else {
                $installationPayload = InstallationPayload::class;
            }

            /** @var Payload $installationPayload */
            $payload = new $installationPayload();
            $payload->setNotificationId(Uuid::uuid4()->toString());

            $result = SendPayload::baseUrl($host)->handle($payload->toArray());

            if (boolval($result)) {
                Config::set('host', $host);
                Config::set('installed', 'false');

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
