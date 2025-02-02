<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class TimeTrackPayload extends Payload
{
    /**
     * Clock script execution time
     */
    public function __construct(
        public string $reference,
        public bool $stop = false,
        public string $screen = 'home'
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

    public function toScreen(): array|Screen
    {
        return new Screen($this->screen);
    }

    public function withLabel(): array|Label
    {
        return new Label($this->reference);
    }
}
