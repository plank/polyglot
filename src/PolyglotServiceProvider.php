<?php

namespace Plank\Polyglot;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Plank\Polyglot\Contracts\Translator;

class PolyglotServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/polyglot.php', 'polyglot');

        $this->app->singleton(Translator::class, fn () => new TranslatorManager($this->app));

        $this->app->alias(Translator::class, 'polyglot');
        $this->app->alias(Translator::class, TranslatorManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/polyglot.php' => config_path('polyglot.php'),
            ], 'polyglot-config');
        }

        $callback = static function (string|array $text, string $target, ?string $source = null) {
            $translator = resolve(Translator::class)->from($source)->to($target);

            return is_array($text) ? $translator->translateBatch($text) : $translator->translate($text);
        };

        Str::macro('translate', $callback);
        Stringable::macro('translate', function (string $target, ?string $source = null) use ($callback) {
            return new Stringable($callback($this->value, $target, $source));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            'polyglot',
            Translator::class,
            TranslatorManager::class,
        ];
    }
}
