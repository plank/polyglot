<?php

namespace Plank\Polyglot\Drivers;

use Plank\Polyglot\Contracts\AbstractTranslator;
use Stichoza\GoogleTranslate\GoogleTranslate;

class StichozaTranslate extends AbstractTranslator
{
    protected GoogleTranslate $client;

    public function __construct()
    {
        $this->client = new GoogleTranslate();
    }

    public function translate(string $string): string
    {
        return $this->client->setSource($this->source)->setTarget($this->target)->translate($string);
    }
}
