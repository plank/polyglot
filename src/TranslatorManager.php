<?php

namespace Plank\Polyglot;

use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use Plank\Polyglot\Contracts\AbstractTranslator;
use Plank\Polyglot\Contracts\NestedTranslator;
use Plank\Polyglot\Contracts\Translator;
use Plank\Polyglot\Drivers\AmazonTranslate;
use Plank\Polyglot\Drivers\GoogleTranslate;
use Plank\Polyglot\Drivers\GoogleV2Translate;
use Plank\Polyglot\Drivers\GoogleV3Translate;
use Plank\Polyglot\Drivers\OpenAiTranslate;
use Plank\Polyglot\Drivers\StackTranslate;
use Plank\Polyglot\Drivers\StichozaTranslate;
use Plank\Polyglot\Exceptions\ValidationException;

/**
 * @mixin AbstractTranslator
 * @mixin Translator
 */
class TranslatorManager extends Manager implements Translator
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('polyglot.default');
    }

    public function translator(?string $translator = null): AbstractTranslator|NestedTranslator
    {
        return $this->driver($translator);
    }

    /**
     * @param  string  $translator
     *
     * {@inheritdoc}
     */
    public function createDriver($translator): AbstractTranslator|NestedTranslator
    {
        if (Str::startsWith($translator, 'stack:')) {
            return $this->stack(...Str::of($translator)->after('stack:')->explode(','));
        }

        $config = $this->configurationFor($translator);
        $driver = $config['driver'];

        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver, $config);
        }

        $method = 'create'.Str::studly($driver).'Driver';
        if (method_exists($this, $method)) {
            return $this->$method($config)->to($config['target'] ?? $this->container->getLocale());
        }

        throw new ValidationException("Translator driver [{$driver}] not supported.");
    }

    /**
     * {@inheritdoc}
     */
    protected function callCustomCreator($driver, $config = null): Translator
    {
        if ($config === null) {
            throw new ValidationException('Custom driver creator requires translator configuration.');
        }

        return $this->customCreators[$driver]($this->container, $config);
    }

    /**
     * Get the translation service connection configuration.
     */
    protected function configurationFor(string $name): array
    {
        $name = $name ?: $this->getDefaultDriver();

        $config = $this->config->get("polyglot.translators.{$name}");

        if ($config === null) {
            throw new ValidationException("Translator [{$name}] not configured.");
        }

        return $config;
    }

    /**
     * @param  array<string>  ...$translators
     */
    public function stack(...$translators): StackTranslate
    {
        $key = 'stack:'.implode(',', $translators);

        if (isset($this->drivers[$key])) {
            return $this->drivers[$key];
        }

        $config = $this->config->get('polyglot.translators.stack') ?? [];
        $config['translators'] = $translators;

        return $this->drivers[$key] = $this->createStackDriver($config);
    }

    public function createStackDriver(array $config): StackTranslate
    {
        $translators = $config['translators'] ?? null;

        if ($translators === null) {
            throw new ValidationException('Stack translator requires a list of translator connections.');
        }

        if (is_string($translators)) {
            $translators = explode(',', $translators);
        }

        $translators = array_map(fn ($translator) => $this->translator($translator), $translators);

        return new StackTranslate($translators, $config['retries'] ?? 1, $config['sleep'] ?? 100);
    }

    public function createStichozaDriver(array $config): StichozaTranslate
    {
        return new StichozaTranslate;
    }

    public function createGoogleDriver(array $config): GoogleTranslate
    {
        $version = $config['version'] ?? 'v2';
        $format = $config['format'] ?? 'html';
        $model = $config['model'] ?? 'nmt';

        $client = match ($version) {
            3, 'v3', 'advanced' => new GoogleV3Translate($config['project_id'], [], $format, $model),
            2, 'v2', 'basic' => new GoogleV2Translate($config['key'], $format, $model),
            default => throw new ValidationException("Invalid Google Translate Client version $version"),
        };

        return new GoogleTranslate($client, $config['attribution'] ?? false);
    }

    public function createAmazonDriver(array $config): AmazonTranslate
    {
        $credentials = $config['credentials'] ?? [
            'key' => $config['key'],
            'secret' => $config['secret'],
            'token' => $config['token'] ?? null,
        ];
        $region = $config['region'];
        $format = $config['format'] ?? 'html';
        $version = $config['version'] ?? 'latest';

        return new AmazonTranslate($credentials, $region, $version, $format);
    }

    public function createOpenAiDriver(array $config): OpenAiTranslate
    {
        $key = $config['key'];
        $org = $config['organization'] ?? null;
        $model = $config['model'] ?? 'gpt-3.5-turbo';
        $format = $config['format'] ?? 'html';

        if (($key === null && $org === null) ||
            ($key === $this->config['openai.api_key'] && $org === $this->config['openai.organization'])
        ) {
            $client = $this->container->make('openai');
        } else {
            $client = \OpenAI::client($key, $org);
        }

        return new OpenAiTranslate($client, $format, $model);
    }

    public function translate(string $string): string
    {
        return $this->translator()->translate($string);
    }

    public function translateTo(string $string, string $target, ?string $source = null): string
    {
        return $this->translator()->translateTo($string, $target, $source);
    }

    public function translateBatch(array $strings): array
    {
        return $this->translator()->translateBatch($strings);
    }

    public function translateBatchTo(array $strings, string $target, ?string $source = null): array
    {
        return $this->translator()->translateBatchTo($strings, $target, $source);
    }

    public function languages(?string $target = null): array
    {
        return $this->translator()->languages($target);
    }

    public function format(string $format): Translator
    {
        return $this->translator()->format($format);
    }

    public function from(?string $locale = null): Translator
    {
        return $this->translator()->from($locale);
    }

    public function to(string $locale): Translator
    {
        return $this->translator()->to($locale);
    }
}
