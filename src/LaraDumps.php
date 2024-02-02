<?php

namespace LaraDumps\LaraDumpsCore;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use LaraDumps\LaraDumpsCore\Actions\{Config, InstallLaraDumps, SendPayload, Support};
use LaraDumps\LaraDumpsCore\Concerns\Colors;
use LaraDumps\LaraDumpsCore\Payloads\{BenchmarkPayload,
    ClearPayload,
    CoffeePayload,
    ColorPayload,
    DumpPayload,
    InstallationPayload,
    JsonPayload,
    LabelPayload,
    Payload,
    PhpInfoPayload,
    ScreenPayload,
    TablePayload,
    TimeTrackPayload,
    ValidJsonPayload,
    ValidateStringPayload};
use LaraDumps\LaraDumpsCore\Support\Dumper;
use Ramsey\Uuid\Uuid;

class LaraDumps
{
    use Colors;

    private bool $dispatched = false;

    public function __construct(
        public string $notificationId = '',
        private array $trace = [],
    ) {
        if (!boolval(getenv('DS_RUNNING_IN_TESTS'))) {
            $this->checkForEnvironment();
        }

        if (Config::get('sleep')) {
            $sleep = intval(Config::get('sleep'));
            sleep($sleep);
        }

        $this->notificationId = !empty($notificationId) ? $this->notificationId : Uuid::uuid4()->toString();
    }

    protected function beforeWrite(mixed $args): \Closure
    {
        return function () use ($args) {
            if (is_string($args) && Support::isJson($args)) {
                return [
                    new JsonPayload($args),
                    uniqid(),
                ];
            }

            [$pre, $id] = Dumper::dump($args);

            return [
                new DumpPayload($pre, $args, variableType: gettype($args)),
                $id,
            ];
        };
    }

    private function checkForEnvironment(): void
    {
        try {
            $dotenv = Dotenv::createImmutable(appBasePath(), '.env');
            $dotenv->load();

            if (empty(Config::get('host'))) {
                InstallLaraDumps::install();
            }
        } catch (InvalidPathException) {
            InstallLaraDumps::install();
        }
    }

    public function send(Payload $payload): Payload
    {
        if (!empty($this->trace)) {
            $payload->setTrace($this->trace);
        }

        $payload->setNotificationId($this->notificationId);

        $sendPayload = new SendPayload();

        $response = $sendPayload->handle(
            $payload->toArray()
        );

        if ($response) {
            $payload->setDispatch(true);
        }

        return $payload;
    }

    public function write(mixed $args = null, ?bool $autoInvokeApp = null, array $trace = []): self
    {
        [$payload, $id] = $this->beforeWrite($args)();

        $payload->autoInvokeApp($autoInvokeApp);
        $payload->setDumpId($id);
        $payload->setTrace($trace);

        $this->send($payload);

        return $this;
    }

    /**
     * Send custom color
     */
    public function color(string $color): LaraDumps
    {
        $payload = new ColorPayload($color);
        $this->send($payload);

        return $this;
    }

    /**
     * Add new screen
     */
    public function s(string $screen): LaraDumps
    {
        return $this->toScreen($screen);
    }

    /**
     * Add new screen
     *
     * @param  int  $raiseIn Delay in seconds for the app to raise and focus
     */
    public function toScreen(
        string $screenName,
        int $raiseIn = 0
    ): LaraDumps {
        $payload = new ScreenPayload($screenName, $raiseIn);
        $this->send($payload);

        return $this;
    }

    /**
     * Send custom label
     */
    public function label(string $label): LaraDumps
    {
        $payload = new LabelPayload($label);
        $payload->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);

        return $this;
    }

    /**
     * Send dump and die
     */
    public function die(string $status = ''): void
    {
        exit($status);
    }

    /**
     * Clear screen
     */
    public function clear(): LaraDumps
    {
        $this->send(new ClearPayload());

        return $this;
    }

    /**
     * Grab a coffee!
     */
    public function coffee(): LaraDumps
    {
        $this->send(new CoffeePayload());

        return $this;
    }

    /**
     * Send JSON data and validate
     */
    public function isJson(): LaraDumps
    {
        $payload = new ValidJsonPayload();
        $payload->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);

        return $this;
    }

    /**
     * Checks if content contains string.
     *
     * @param  bool  $caseSensitive Search is case-sensitive
     * @param  bool  $wholeWord Search for the whole words
     */
    public function contains(string $content, bool $caseSensitive = false, bool $wholeWord = false): LaraDumps
    {
        $payload = new ValidateStringPayload('contains');
        $payload->setContent($content)
            ->setCaseSensitive($caseSensitive)
            ->setWholeWord($wholeWord)
            ->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);

        return $this;
    }

    /**
     * Send PHPInfo
     */
    public function phpinfo(): LaraDumps
    {
        $payload = new PhpInfoPayload();
        $payload->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);

        return $this;
    }

    /**
     * Send Table
     */
    public function table(mixed $data = [], string $name = ''): LaraDumps
    {
        $payload = new TablePayload($data, $name);
        $payload->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);

        return $this;
    }

    /**
     * Starts clocking a code block execution time
     *
     * @param  string  $reference Unique name for this time clocking
     */
    public function time(string $reference): void
    {
        $payload = new TimeTrackPayload($reference);
        $payload->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);
        $this->label($reference);
    }

    /**
     * Stops clocking a code block execution time
     *
     * @param  string  $reference Unique name called on ds()->time()
     */
    public function stopTime(string $reference): void
    {
        $payload = new TimeTrackPayload($reference, true);
        $payload->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);
    }

    /**
     * Benchmarking
     */
    public function benchmark(mixed ...$args): self
    {
        $benchmarkPayload = new BenchmarkPayload($args);
        $this->send($benchmarkPayload);

        return $this;
    }

    public function getDispatch(): bool
    {
        return $this->dispatched;
    }

    public function configure(): static
    {
        if (class_exists(\LaraDumps\LaraDumps\Payloads\InstallationPayload::class)) {
            $installationPayload = \LaraDumps\LaraDumps\Payloads\InstallationPayload::class;
        } else {
            $installationPayload = InstallationPayload::class;
        }

        /** @phpstan-ignore-next-line  */
        $installationPayloadInstance = new $installationPayload($_ENV['APP_NAME'] ?? "");

        /** @phpstan-ignore-next-line  */
        $dispatched       = $this->send($installationPayloadInstance);
        $this->dispatched = $dispatched->getDispatch();

        return $this;
    }
}
