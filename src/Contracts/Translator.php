<?php

namespace Plank\Polyglot\Contracts;

interface Translator
{
    public function translate(string $string): string;

    public function translateTo(string $string, string $target, ?string $source = null): string;

    public function translateBatch(array $strings): array;

    public function translateBatchTo(array $strings, string $target, ?string $source = null): array;
}
