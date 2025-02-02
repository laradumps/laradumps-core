<?php

namespace LaraDumps\LaraDumpsCore\Payloads;

class ValidateStringPayload extends Payload
{
    protected string $content;

    protected bool $caseSensitive = false;

    protected bool $wholeWord = false;

    public function __construct(
        private string $type,
        private string $screen = 'home',
        private string $label = '',
    ) {
    }

    public function type(): string
    {
        return 'validate';
    }

    /** @return array<string|boolean> */
    public function content(): array
    {
        return [
            'type'              => $this->type,
            'content'           => $this->content ?? '',
            'is_case_sensitive' => $this->caseSensitive,
            'is_whole_word'     => $this->wholeWord,
        ];
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setCaseSensitive(bool $caseSensitive = true): self
    {
        $this->caseSensitive = $caseSensitive;

        return $this;
    }

    public function setWholeWord(bool $wholeWord = true): self
    {
        $this->wholeWord = $wholeWord;

        return $this;
    }

    public function toScreen(): array|Screen
    {
        return new Screen($this->screen);
    }

    public function withLabel(): array|Label
    {
        return new Label($this->label);
    }
}
