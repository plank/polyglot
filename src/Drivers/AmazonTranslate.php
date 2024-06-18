<?php

namespace Plank\Polyglot\Drivers;

use Aws\Result;
use Aws\Translate\TranslateClient;
use Plank\Polyglot\Contracts\AbstractTranslator;
use Plank\Polyglot\Exceptions\ValidationException;

class AmazonTranslate extends AbstractTranslator
{
    protected TranslateClient $client;

    public function __construct(array $credentials, string $region, string $version = 'latest', string $format = 'html')
    {
        $this->client = new TranslateClient([
            'region' => $region,
            'version' => $version,
            'credentials' => $credentials,
        ]);

        $this->format = $format;
    }

    public function translate(string $text): string
    {
        $response = $this->sendTranslateRequest($text, [
            'TargetLanguageCode' => $this->target,
            'SourceLanguageCode' => $this->source,
        ]);

        return $response['TranslatedText'];
    }

    public function translateBatch(array $strings): array
    {
        foreach ($strings as $key => $text) {
            $strings[$key] = $this->translate($text);
        }

        return $strings;
    }

    public function sendTranslateRequest(string $text, array $options): Result
    {
        if ($options['SourceLanguageCode'] === null) {
            $options['SourceLanguageCode'] = 'auto';
        }

        if ($options['TargetLanguageCode'] === null) {
            throw new ValidationException('Amazon translation requires target locale.');
        }

        $options['Text'] = $text;

        $response = $this->client->translateText($options);

        return $response;
    }

    public function languages($target = null): array
    {
        $response = $this->client->listLanguages($target ? ['DisplayLanguageCode' => $target] : []);

        return $response['Languages'];
    }
}
