<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class TimeTrackPayload extends Payload
{
    /**
     * Clock script executiontime
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
            'label'      => $this->reference,
        ];

        if ($this->stop) {
            $content['end_time'] = microtime(true);
        }

        return $content;
    }
}
