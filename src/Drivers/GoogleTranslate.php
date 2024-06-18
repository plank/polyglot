<?php

namespace Plank\Polyglot\Drivers;

use Plank\Polyglot\Contracts\NestedTranslator;
use Plank\Polyglot\Contracts\Translator;

class GoogleTranslate extends NestedTranslator
{
    protected Translator $client;

    public function __construct(GoogleV2Translate|GoogleV3Translate $client)
    {
        $this->client = $client;
    }

    /**
     * google specifies html attribution guidelines
     */
    protected function applyHtmlAttribution(string $html, string $target, string $source = 'auto'): string
    {
        return "<div lang='$target-x-mtfrom-$source'>$html</div>";
    }

    public function translate(string $text): string
    {
        $output = parent::translate($text);

        if ($this->getFormat() === 'html') {
            $output = $this->applyHtmlAttribution($output, $this->getTarget(), $this->getSource());
        }

        return $output;
    }

    public function translateBatch(array $strings): array
    {
        $output = parent::translateBatch($strings);

        if ($this->getFormat() === 'html') {
            $target = $this->getTarget();
            $source = $this->getSource() ?? 'auto';
            $output = array_map(fn ($string) => $this->applyHtmlAttribution($string, $target, $source), $output);
        }

        return $output;
    }
}
