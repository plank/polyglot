<?php

namespace Plank\Polyglot\Contracts;

use Illuminate\Support\Traits\ForwardsCalls;

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

    public function translateBatch(array $strings): array
    {
        return $this->client->translateBatch($strings);
    }

    public function translateBatchTo(array $strings, string $target, ?string $source = null): array
    {
        return $this->client->translateBatchTo($strings, $target, $source);
    }

    public function __call($method, $arguments)
    {
        return $this->forwardDecoratedCallTo($this->client, $method, $arguments);
    }
}
