<p align="center"><a href="https://plank.co"><img src="art/polyglot.png" width="100%"></a></p>

<p align="center">
<a href="https://packagist.org/packages/plank/polyglot"><img src="https://img.shields.io/packagist/php-v/plank/polyglot?color=%23fae370&label=php&logo=php&logoColor=%23fff" alt="PHP Version Support"></a>
<a href="https://laravel.com/docs/11.x/releases#support-policy"><img src="https://img.shields.io/badge/laravel-9.x,%2010.x,%2011.x-%2343d399?color=%23f1ede9&logo=laravel&logoColor=%23ffffff" alt="PHP Version Support"></a>
<a href="https://github.com/plank/polyglot/actions?query=workflow%3Arun-tests"><img src="https://img.shields.io/github/actions/workflow/status/plank/polyglot/run-tests.yml?branch=main&&color=%23bfc9bd&label=run-tests&logo=github&logoColor=%23fff" alt="GitHub Workflow Tests Status"></a>
<a href="https://codeclimate.com/github/plank/polyglot/test_coverage"><img src="https://img.shields.io/codeclimate/coverage/plank/polyglot?color=%23ff9376&label=test%20coverage&logo=code-climate&logoColor=%23fff" /></a>
<a href="https://codeclimate.com/github/plank/polyglot/maintainability"><img src="https://img.shields.io/codeclimate/maintainability/plank/polyglot?color=%23528cff&label=maintainablility&logo=code-climate&logoColor=%23fff" /></a>
</p>

# Laravel Polyglot

:warning: Package is under active development. Wait for v1.0.0 for production use. :warning:

Polyglot provides a simple way to access a multitude of cloud machine translation services.
It is designed to be easily extensible, allowing you to add new drivers as needed.

Currently supported drivers:
- Stichoza Google Translate PHP Package
- Google Cloud Translation API (default to v2)
- Amazon Translate
- OpenAI

## Installation

You can install the package via composer, and it will automatically register itself.

```bash
composer require plank/polyglot
```

Afterward, you can publish the config file with:

```bash
php artisan vendor:publish --tag="polyglot-config"
```

## Usage

If you simply need to translate a piece of text, this package provides a few convenient methods to do so.

- Using the macro on laravel's `Str` class or via the helper function `str()`
    
    ```php
    > Str::translate('Hello!', 'fr');
    = "Bonjour!"

    > str('hi')->translate('fr');
    = "Salut"
    ```

- Using the facade, you can access any of your configured translators and translate text between languages

    ```php
    Polyglot::translator('google')->from('en')->to('fr')->translate('Hello!');
    Polyglot::translator('amazon')->translateTo('Hello!', target: 'fr', source: 'en');
    ```
  
- Using the stack translator, you can chain multiple services as fallbacks in case one or more of them fail

    ```php
    Polyglot::stack('google', 'amazon')->from('en')->to('fr')->translate('Hello!');
    Polyglot::stack('google', 'amazon')->translateTo('Hello!', 'fr');
    ```

    A stack can also be configured as a translator in your `/config/polyglot.php`.
    ```php
        'translators' => [
            'stack' => [
                'driver' => 'stack',
                'translators' => ['amazon', 'google', 'gpt'],
                'retries' => 3,
                'sleep' => 100,
            ],
            ...
        ]
    ```
    ```php
    > Polyglot::translator('stack')->from('auto')->to('it')->translate('Hello!');
    = "Ciao!"
    ```

### Other Methods

- `Translator::translateBatch` - Translate an array of strings, set target language with `to` method
- `Translator::translateBatchTo` - Translate an array of strings to a specific language
- `AbstractTranslator::to`, `from`, `format` - Fluent API for setting translator options
- `AbstractTranslator::languages` - Get a list of supported languages, if locale is passed it will return the supported languages for that locale
- `sendTranslateRequest` - Some drivers allow you to access the underlying response from the translation service

### Custom Drivers

You can extend polyglot and provide your own translator drivers. To do so, follow these steps:

1. Create a new class that extends `Plank\Polyglot\Contracts\AbstractTranslator`
   and implement the missing methods specified by the `Plank\Polyglot\Contracts\Translator` interface.
2. Then, in a service provider, call the `Plank\Polyglot\Polyglot::extend` method to register your new driver.

    ```php
    Polyglot::extend('azure', static function (Container $app, array $config) {
        $key = $config['key'];

        return new AzureTranslator($key);
    });
    ```
3. Now you can add a new translator with the driver you just created by adding it to the `config/polyglot.php` file:

    ```php
    return [
        'default' => env('POLYGLOT_DEFAULT', 'bing'),
        'translators' => [
            'microsoft' => [
                'driver' => 'azure',
                'key' => env('BING_TRANSLATE_API_KEY'),
            ],
            ...
        ],
    ];
    ```
4. And finally use it in your code:

    ```php
    Polyglot::translator('bing')->from('en')->to('fr')->translate('Hello!');
    ```

## Testing

```bash
composer test
```

## Credits

- [a-drew](https://github.com/a-drew)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Security Vulnerabilities

If you discover a security vulnerability within siren, please send an e-mail to [security@plank.co](mailto:security@plank.co).
All security vulnerabilities will be promptly addressed.

## Check Us Out!

<a href="https://plank.co/open-source/learn-more-image">
    <img src="https://plank.co/open-source/banner">
</a>

&nbsp;

Plank focuses on impactful solutions that deliver engaging experiences to our clients and their users.
We're committed to innovation, inclusivity, and sustainability in the digital space.
[Learn more](https://plank.co/open-source/learn-more-link) about our mission to improve the web.
