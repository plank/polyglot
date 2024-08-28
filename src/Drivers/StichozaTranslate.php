<?php

namespace Plank\Polyglot\Drivers;

use GuzzleHttp\Client;
use Plank\Polyglot\Contracts\AbstractTranslator;
use Stichoza\GoogleTranslate\GoogleTranslate;

class StichozaTranslate extends AbstractTranslator
{
    protected int $limit = 15000;

    protected GoogleTranslate $client;

    public function __construct()
    {
        $this->client = new GoogleTranslate;
    }

    public function translate(string $text): string
    {
        if (strlen($text) > $this->limit) {
            return implode('', $this->translateBatch($text));
        }

        return $this->client->setSource($this->source)->setTarget($this->target)->translate($text);
    }

    public function languages($target = null): array
    {
        return $this->client->languages($target);
    }
}
