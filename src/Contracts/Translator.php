<?php

namespace Plank\Polyglot\Contracts;

interface Translator
{
    public function translate(string $string): string;

    public function translateTo(string $string, string $target, ?string $source = null): string;

    public function translateBatch(array $strings): array;

    public function translateBatchTo(array $strings, string $target, ?string $source = null): array;

    public function languages(?string $target = null): array;

    public function format(string $format): self;

    public function to(string $locale): self;

    public function from(?string $locale = null): self;
}
