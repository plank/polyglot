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
        $limit = $this->limit;
        $format = $this->format;
        if ($this->format === 'html' && $text === strip_tags($text)) {
            $this->format = 'text';
        }

        $this->limit = $this->format === 'text' ? 10000 : 102400;

        $options = ['TargetLanguageCode' => $this->target, 'SourceLanguageCode' => $this->source];
        if (strlen($text) > $this->limit) {
            $this->format = $format;
            $response = ['TranslatedText' => implode('', $this->translateBatch($text))];
        } elseif ($this->format !== 'text') {
            $options['Document'] = ['ContentType' => ($this->format === 'html' ? 'text/html' : 'text/plain')];
            $response = $this->sendTranslateDocumentRequest($text, $options);
        } else {
            $response = $this->sendTranslateRequest($text, $options);
        }

        $this->limit = $limit;
        $this->format = $format;

        return $response['TranslatedText'] ?? $response['TranslatedDocument']['Content'];
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

    public function sendTranslateDocumentRequest(string $content, array $options): Result
    {
        if (empty($options['SourceLanguageCode'])) {
            $options['SourceLanguageCode'] = 'auto';
        }

        if (empty($options['TargetLanguageCode'])) {
            throw new ValidationException('Amazon document translation requires target locale.');
        }

        if (! is_array($options['Document']) || empty($options['Document']['ContentType'])) {
            throw new ValidationException('Amazon document translation requires a Document with ContentType.');
        }

        $options['Document']['Content'] = $content;

        return $this->client->translateDocument($options);
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
