<?php

namespace Plank\Polyglot\Drivers;

use Plank\Polyglot\Contracts\NestedTranslator;
use Plank\Polyglot\Contracts\Translator;

class StackTranslate extends NestedTranslator
{
    protected Translator $client;

    /** @var Translator[] */
    protected array $clients;

    protected int $retries;

    protected int $sleep;

    public function __construct(array $clients, $retries = 1, $sleep = 100)
    {
        $this->client = $clients[0];
        $this->clients = $clients;
        $this->retries = $retries;
        $this->sleep = $sleep;
    }

    public function translate(string $text): string
    {
        return $this->tryAllClients(fn (Translator $client) => $client->translate($text));
    }

    public function translateTo(string $text, string $target, ?string $source = null): string
    {
        return $this->tryAllClients(fn (Translator $client) => $client->translateTo($text, $target, $source));
    }

    public function translateBatch(array $strings): array
    {
        return $this->tryAllClients(fn (Translator $client) => $client->translateBatch($strings));
    }

    public function translateBatchTo(array $text, string $target, ?string $source = null): array
    {
        return $this->tryAllClients(fn (Translator $client) => $client->translateBatchTo($text, $target, $source));
    }

    protected function tryAllClients($callback): mixed
    {
        $times ??= $this->retries;
        $sleep ??= $this->sleep;

        foreach ($this->clients as $client) {
            try {
                return retry($times, static fn ($attempts) => $callback($client), $sleep);
            } catch (\Throwable $e) {
                continue;
            }
        }
    }

    public function setSource(?string $source = null): self
    {
        foreach ($this->clients as $client) {
            $client->setSource($source);
        }

        return $this;
    }

    public function setTarget(string $target): self
    {
        foreach ($this->clients as $client) {
            $client->setTarget($target);
        }

        return $this;
    }

    public function setFormat(string $format): self
    {
        foreach ($this->clients as $client) {
            $client->setFormat($format);
        }

        return $this;
    }
}
