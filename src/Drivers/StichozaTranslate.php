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
        $client = new Client;
        $menu = 'sl'; // 'tl';
        $url = "https://translate.google.com/m?mui=$menu&hl=$target";

        $html = $client->get($url)->getBody()->getContents();
        // add a meta tag to ensure the HTML content is treated as UTF-8, fixes xpath node values
        $html = preg_replace('/<head>/i', '<head><meta charset="UTF-8">', $html);

        // Prepare to crawl DOM
        $dom = new \DOMDocument;
        $dom->loadHTML($html);
        $nodes = (new \DOMXPath($dom))->query('//div[@class="language-item"]/a');

        $languages = [];
        foreach ($nodes as $node) {
            $code = strtok(str_after($node->getAttribute('href'), "$menu="), '&');
            if ($target === null) {
                $languages[] = $code;
            } else {
                $languages[$code] = $node->nodeValue;
            }
        }

        return $languages;
    }
}
