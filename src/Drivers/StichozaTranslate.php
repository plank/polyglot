<?php

namespace Plank\Polyglot\Drivers;

use GuzzleHttp\Client;
use Plank\Polyglot\Contracts\AbstractTranslator;
use Stichoza\GoogleTranslate\GoogleTranslate;

class StichozaTranslate extends AbstractTranslator
{
    protected GoogleTranslate $client;

    public function __construct()
    {
        $this->client = new GoogleTranslate;
    }

    public function translate(string $text): string
    {
        if (strlen($text) > 15000) {
            $strings = str_split($text, 15000);

            return implode('', $this->translateBatch($strings));
        }

        return $this->client->setSource($this->source)->setTarget($this->target)->translate($text);
    }

    public function languages($target = null): array
    {
        return $this->client->languages($target);
    }
}
