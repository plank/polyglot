<?php

namespace Plank\Polyglot\Drivers;

use Plank\Polyglot\Contracts\NestedTranslator;
use Plank\Polyglot\Contracts\Translator;

class GoogleTranslate extends NestedTranslator
{
    protected Translator $client;

    protected bool $attribution;

    public function __construct(GoogleV2Translate|GoogleV3Translate $client, bool $attribution = false)
    {
        $this->client = $client;
        $this->attribution = $attribution;
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

        if ($this->attribution && $this->getFormat() === 'html') {
            $output = $this->applyHtmlAttribution($output, $this->getTarget(), $this->getSource());
        }

        return $output;
    }

    public function translateTo(string $text, string $target, ?string $source = null): string
    {
        $output = parent::translateTo($text, $target, $source);

        if ($this->attribution && $this->getFormat() === 'html') {
            $output = $this->applyHtmlAttribution($output, $target, $source ?? $this->getSource());
        }

        return $output;
    }

    public function translateBatch(array $strings): array
    {
        $output = parent::translateBatch($strings);

        if ($this->attribution && $this->getFormat() === 'html') {
            $target = $this->getTarget();
            $source = $this->getSource() ?? 'auto';
            $output = array_map(fn ($string) => $this->applyHtmlAttribution($string, $target, $source), $output);
        }

        return $output;
    }

    public function translateBatchTo(array $strings, string $target, ?string $source = null): array
    {
        $output = parent::translateBatchTo($strings, $target, $source);

        if ($this->attribution && $this->getFormat() === 'html') {
            $source = $source ?? $this->getSource() ?? 'auto';
            $output = array_map(fn ($string) => $this->applyHtmlAttribution($string, $target, $source), $output);
        }

        return $output;
    }
}
