<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class TimeTrackPayload extends Payload
{
    /**
     * Clock script execution time
     */
    public function __construct(
        public string $reference,
        public bool $stop = false
    ) {
    }

    public function type(): string
    {
        return 'time_track';
    }

    /** @return array<string, mixed> */
    public function content(): array
    {
        $content = [
            'tracker_id' => uniqid(),
            'time'       => microtime(true),
        ];

        if ($this->stop) {
            $content['end_time'] = microtime(true);
        }

        return $content;
    }

    public function screen(): array|Screen
    {
        return new Screen('screen 1');
    }

    public function label(): array|Label
    {
        return new Label($this->reference);
    }
}
