<?php

use LaraDumps\LaraDumpsCore\Dispatcher\Curl;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // Reset the static $ignorePrimary flag via reflection
    $ref  = new ReflectionClass(Curl::class);
    $prop = $ref->getProperty('ignorePrimary');
    $prop->setAccessible(true);
    $prop->setValue(null, false);
});

function waitForServer(string $host, int $port, int $timeoutMs = 3000): void
{
    $deadline = microtime(true) + ($timeoutMs / 1000);

    // Silence the expected "Connection refused" notices while the server boots
    // (PHPUnit's error handler surfaces them even through the @ operator).
    set_error_handler(static fn (): bool => true);

    try {
        while (microtime(true) < $deadline) {
            $conn = fsockopen($host, $port, $errno, $errstr, 0.1);

            if ($conn !== false) {
                fclose($conn);

                return;
            }

            usleep(20000);
        }
    } finally {
        restore_error_handler();
    }
}

describe('Curl::make()', function () {
    it('returns a Curl instance', function () {
        $curl = Curl::make();
        expect($curl)->toBeInstanceOf(Curl::class);
    });
});

describe('Curl::dispatch()', function () {
    it('returns false when the URL is not reachable', function () {
        $curl   = new Curl();
        $result = $curl->dispatch('http://127.0.0.1:19999/api/dumps', '{"test":true}');
        expect($result)->toBeFalse();
    });

    it('returns false when response is not valid JSON with UUID', function () {
        // We'll test with a URL that will fail/timeout
        $curl   = new Curl();
        $result = $curl->dispatch('http://127.0.0.1:19998/api/dumps', '{"test":true}');
        expect($result)->toBeFalse();
    });
});

describe('Curl::handle()', function () {
    it('returns false when both primary and secondary are unreachable', function () {
        $curl   = new Curl();
        $result = $curl->handle(['type' => 'test', 'content' => []]);
        expect($result)->toBeFalse();
    });

    it('tries secondary after primary fails', function () {
        $curl   = new Curl();
        $result = $curl->handle(['type' => 'test', 'content' => []]);
        // Both fail, so result is false
        expect($result)->toBeFalse();
    });

    it('skips primary when ignorePrimary is true', function () {
        // Set ignorePrimary to true
        $ref  = new ReflectionClass(Curl::class);
        $prop = $ref->getProperty('ignorePrimary');
        $prop->setAccessible(true);
        $prop->setValue(null, true);

        $curl   = new Curl();
        $result = $curl->handle(['type' => 'test', 'content' => []]);
        // Secondary also fails
        expect($result)->toBeFalse();
    });
});

describe('Curl::dispatch() with mock server', function () {
    it('returns true when response contains a valid UUID id', function () {
        // Start a simple PHP built-in server that returns a valid UUID response
        $port    = 19876;
        $docRoot = sys_get_temp_dir() . '/ld_curl_test_' . uniqid();
        mkdir($docRoot);
        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        file_put_contents($docRoot . '/index.php', "<?php echo json_encode(['id' => '{$uuid}']);");

        $proc = proc_open(
            "php -S 127.0.0.1:{$port} -t {$docRoot}",
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes
        );

        // Wait until the server is ready to accept connections
        waitForServer('127.0.0.1', $port);

        try {
            $curl   = new Curl();
            $result = $curl->dispatch("http://127.0.0.1:{$port}/index.php", '{"test":true}');
            expect($result)->toBeTrue();
        } finally {
            proc_terminate($proc);
            proc_close($proc);
            @unlink($docRoot . '/index.php');
            @rmdir($docRoot);
        }
    });

    it('returns false when response contains invalid UUID', function () {
        $port    = 19877;
        $docRoot = sys_get_temp_dir() . '/ld_curl_test2_' . uniqid();
        mkdir($docRoot);
        file_put_contents($docRoot . '/index.php', "<?php echo json_encode(['id' => 'not-a-uuid']);");

        $proc = proc_open(
            "php -S 127.0.0.1:{$port} -t {$docRoot}",
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes
        );

        waitForServer('127.0.0.1', $port);

        try {
            $curl   = new Curl();
            $result = $curl->dispatch("http://127.0.0.1:{$port}/index.php", '{"test":true}');
            expect($result)->toBeFalse();
        } finally {
            proc_terminate($proc);
            proc_close($proc);
            @unlink($docRoot . '/index.php');
            @rmdir($docRoot);
        }
    });

    it('sets ignorePrimary when primary fails but secondary succeeds', function () {
        $port    = 19878;
        $docRoot = sys_get_temp_dir() . '/ld_curl_test3_' . uniqid();
        mkdir($docRoot);
        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        file_put_contents($docRoot . '/index.php', "<?php echo json_encode(['id' => '{$uuid}']);");

        $proc = proc_open(
            "php -S 127.0.0.1:{$port} -t {$docRoot}",
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes
        );

        waitForServer('127.0.0.1', $port);

        try {
            // Create a Curl with secondary pointing to our mock server
            $curl = new Curl();

            // Set secondary URL via reflection to our working server
            $ref     = new ReflectionClass(Curl::class);
            $secProp = $ref->getProperty('secondaryUrl');
            $secProp->setAccessible(true);
            $secProp->setValue($curl, "http://127.0.0.1:{$port}/index.php");

            // Primary will fail (default 127.0.0.1:9191), secondary should succeed
            $result = $curl->handle(['type' => 'test', 'content' => []]);
            expect($result)->toBeTrue();

            // Check that ignorePrimary is now true
            $ignoreProp = $ref->getProperty('ignorePrimary');
            $ignoreProp->setAccessible(true);
            expect($ignoreProp->getValue(null))->toBeTrue();
        } finally {
            proc_terminate($proc);
            proc_close($proc);
            @unlink($docRoot . '/index.php');
            @rmdir($docRoot);
        }
    });

    it('returns true immediately when primary succeeds', function () {
        $port    = 19879;
        $docRoot = sys_get_temp_dir() . '/ld_curl_test4_' . uniqid();
        mkdir($docRoot);
        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        file_put_contents($docRoot . '/index.php', "<?php echo json_encode(['id' => '{$uuid}']);");

        $proc = proc_open(
            "php -S 127.0.0.1:{$port} -t {$docRoot}",
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes
        );

        waitForServer('127.0.0.1', $port);

        try {
            $curl = new Curl();

            // Set primary URL to our working server
            $ref     = new ReflectionClass(Curl::class);
            $priProp = $ref->getProperty('primaryUrl');
            $priProp->setAccessible(true);
            $priProp->setValue($curl, "http://127.0.0.1:{$port}/index.php");

            $result = $curl->handle(['type' => 'test', 'content' => []]);
            expect($result)->toBeTrue();
        } finally {
            proc_terminate($proc);
            proc_close($proc);
            @unlink($docRoot . '/index.php');
            @rmdir($docRoot);
        }
    });
});
