<?php

namespace Plank\Polyglot\Contracts;

interface Translator
{
    public function translate(string $text): string;

    public function translateTo(string $text, string $target, ?string $source = null): string;

    public function translateBatch(array|string $strings): array;

    public function translateBatchTo(array|string $strings, string $target, ?string $source = null): array;

    public function languages(?string $target = null): array;

    public function format(string $format): self;

    public function to(string $locale): self;

    public function from(?string $locale = null): self;
}
