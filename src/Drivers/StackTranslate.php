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

    public function translateBatchTo(array $strings, string $target, ?string $source = null): array
    {
        return $this->tryAllClients(fn (Translator $client) => $client->translateBatchTo($strings, $target, $source));
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

    public function languages($target = null): array
    {
        return array_reduce($this->clients, static function ($carry, $client) use ($target) {
            $languages = $client->languages($target);

            return (is_null($carry) || count($languages) < count($carry)) ? $languages : $carry;
        });
    }

    public function format(string $format): self
    {
        foreach ($this->clients as $client) {
            $client->format($format);
        }

        return $this;
    }

    public function from(?string $source = null): self
    {
        foreach ($this->clients as $client) {
            $client->from($source);
        }

        return $this;
    }

    public function to(string $target): self
    {
        foreach ($this->clients as $client) {
            $client->to($target);
        }

        return $this;
    }
}
