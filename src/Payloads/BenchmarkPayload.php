<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

use LaraDumps\LaraDumpsCore\Actions\Dumper;

class BenchmarkPayload extends Payload
{
    public function __construct(
        private mixed $args,
        private string $screen = 'home',
        private string $label = 'Benchmark',
    ) {
    }

    public function type(): string
    {
        return 'table_v2';
    }

    public function content(): array
    {
        $results      = [];
        $fastestLabel = '';
        $fastestTime  = PHP_INT_MAX;

        /** @var array $closures */
        $closures = $this->args;

        if (count($closures) === 1 && is_array($closures[0])) {
            $closures = $closures[0];
        }

        foreach ($closures as $label => $closure) {
            $startsAt = microtime(true);
            /** @var callable $result */
            $result = $closure();

            $endsAt    = microtime(true);
            $totalTime = round(($endsAt - $startsAt) * 1000);
            $label     = is_int($label) ? 'Closure ' . $label : $label;

            /** @var \DateTime $startDateTime */
            $startDateTime = \DateTime::createFromFormat('U.u', sprintf('%.6f', $startsAt));
            /** @var \DateTime $endDateTime */
            $endDateTime = \DateTime::createFromFormat('U.u', sprintf('%.6f', $endsAt));

            $results[$label] = [
                'Start Time' => $startDateTime->format('Y-m-d H:i:s'),
                'End Time'   => $endDateTime->format('Y-m-d H:i:s'),
                'Total Time' => $totalTime . ' ms',
                'Result'     => $result,
            ];

            if ($totalTime < $fastestTime) {
                $fastestLabel = $label;
                $fastestTime  = $totalTime;
            }
        }

        $results['Fastest'] = $fastestLabel;

        return [
            'values' => array_map(fn ($result) => Dumper::dump($result), $results),
        ];
    }

    public function toScreen(): Screen
    {
        return new Screen($this->screen);
    }

    public function withLabel(): Label
    {
        return new Label($this->label);
    }
}
