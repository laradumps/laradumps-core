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

    protected function beforeWrite($args): \Closure
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
                new DumpPayload($pre, $args),
                $id,
            ];
        };
    }

    private function checkForEnvironment(): void
    {
        try {
            $dotenv = Dotenv::createImmutable('./', '.env');
            $dotenv->load();

            if (empty(Config::get('host'))) {
                InstallLaraDumps::install();
            }
        } catch (InvalidPathException) {
            InstallLaraDumps::install();
        }
    }

    public function send(array|Payload $payload): array|Payload
    {
        if (!$payload instanceof Payload) {
            return $payload;
        }

        if (!empty($this->trace)) {
            $payload->setTrace($this->trace);
        }

        $payload->notificationId($this->notificationId);

        $payload = $payload->toArray();

        $sendPayload = new SendPayload();
        $sendPayload->handle($payload);

        return $payload;
    }

    public function write(mixed $args = null, ?bool $autoInvokeApp = null, array $trace = []): self
    {
        [$payload, $id] = $this->beforeWrite($args)();

        $payload->autoInvokeApp($autoInvokeApp);
        $payload->dumpId($id);
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
        $payload = new TimeTrackPayload();
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
        $payload = new TimeTrackPayload(true);
        $payload->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);
        $this->label($reference);
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
}
