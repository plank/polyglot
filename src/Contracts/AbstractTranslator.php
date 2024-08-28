<?php

namespace Plank\Polyglot\Contracts;

abstract class AbstractTranslator implements Translator
{
    protected int $limit = 25000;

    protected string $format = 'text';

    protected string $source = 'auto';

    protected string $target;

    abstract public function translate(string $text): string;

    public function translateTo(string $text, string $target, ?string $source = null): string
    {
        if ($source !== null) {
            $this->from($source);
        }

        return $this->to($target)->translate($text);
    }

    public function translateBatch(array|string $strings): array
    {
        if (is_string($strings)) {
            $strings = match ($this->format) {
                'html' => html_split($strings, $this->limit),
                default => str_split($strings, $this->limit),
            };
        }

        return array_map(fn ($string) => $this->translate($string), $strings);
    }

    public function translateBatchTo(array|string $strings, string $target, ?string $source = null): array
    {
        if ($source !== null) {
            $this->from($source);
        }

        return $this->to($target)->translateBatch($strings);
    }

    public function languages($target = null): array
    {
        return [];
    }

    public function format(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function from(?string $source = null): self
    {
        $this->source = $source ?? 'auto';

        return $this;
    }

    public function to(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }
}
