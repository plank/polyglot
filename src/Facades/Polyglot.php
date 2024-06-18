<?php

namespace Plank\Polyglot\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Plank\Polyglot\TranslatorManager
 */
class Polyglot extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Plank\Polyglot\TranslatorManager::class;
    }
}
