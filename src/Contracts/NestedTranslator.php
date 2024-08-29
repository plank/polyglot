<?php

namespace Plank\Polyglot\Contracts;

use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @mixin Translator
 */
abstract class NestedTranslator implements Translator
{
    use ForwardsCalls;

    protected Translator $client;

    public function translate(string $text): string
    {
        return $this->client->translate($text);
    }

    public function translateTo(string $text, string $target, ?string $source = null): string
    {
        return $this->client->translateTo($text, $target, $source);
    }

    public function translateBatch(array|string $strings): array
    {
        return $this->client->translateBatch($strings);
    }

    public function translateBatchTo(array|string $strings, string $target, ?string $source = null): array
    {
        return $this->client->translateBatchTo($strings, $target, $source);
    }

    public function languages($target = null): array
    {
        return $this->client->languages($target);
    }

    public function format(string $format): self
    {
        $this->client->format($format);

        return $this;
    }

    public function from(?string $locale = null): self
    {
        $this->client->from($locale);

        return $this;
    }

    public function to(string $locale): self
    {
        $this->client->to($locale);

        return $this;
    }

    public function __call($method, $arguments)
    {
        return $this->forwardDecoratedCallTo($this->client, $method, $arguments);
    }
}
