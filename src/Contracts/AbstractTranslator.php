<?php

namespace Plank\Polyglot\Contracts;

abstract class AbstractTranslator implements Translator
{
    protected string $format = 'text';

    protected string $source = 'auto';

    protected string $target;

    abstract public function translate(string $string): string;

    public function translateTo(string $string, string $target, ?string $source = null): string
    {
        if ($source !== null) {
            $this->setSource($source);
        }

        return $this->setTarget($target)->translate($string);
    }

    public function translateBatch(array $strings): array
    {
        return array_map(fn ($string) => $this->translate($string), $strings);
    }

    public function translateBatchTo(array $strings, string $target, ?string $source = null): array
    {
        if ($source !== null) {
            $this->setSource($source);
        }

        return $this->setTarget($target)->translateBatch($strings);
    }

    public function languages($target = null): array
    {
        return [];
    }

    public function format(string $format): self
    {
        return $this->setFormat($format);
    }

    public function to(string $locale): self
    {
        return $this->setTarget($locale);
    }

    public function from(?string $locale = null): self
    {
        return $this->setSource($locale);
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function setSource(?string $source = null): self
    {
        $this->source = $source ?? 'auto';

        return $this;
    }

    public function setTarget(string $target): self
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
