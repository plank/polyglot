<?php

namespace Plank\Polyglot\Drivers;

use Aws\Result;
use Aws\Translate\TranslateClient;
use Plank\Polyglot\Contracts\AbstractTranslator;
use Plank\Polyglot\Exceptions\ValidationException;

class AmazonTranslate extends AbstractTranslator
{
    protected int $limit = 10000;

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
        if (strlen($text) > $this->limit) {
            return implode('', $this->translateBatch($text));
        }

        $response = $this->sendTranslateRequest($text, [
            'TargetLanguageCode' => $this->target,
            'SourceLanguageCode' => $this->source,
        ]);

        return $response['TranslatedText'];
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

        return $this->client->translateText($options);
    }

    public function languages($target = null): array
    {
        $languages = collect($this->sendLanguagesRequest($target));

        if ($target === null) {
            return $languages->pluck('LanguageCode')->toArray();
        }

        return $languages->pluck('LanguageName', 'LanguageCode')->toArray();
    }

    public function sendLanguagesRequest($target = null): array
    {
        $response = $this->client->listLanguages($target ? ['DisplayLanguageCode' => $target] : []);

        return $response['Languages'];
    }
}
