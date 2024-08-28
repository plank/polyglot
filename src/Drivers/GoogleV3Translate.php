<?php

namespace Plank\Polyglot\Drivers;

use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\GetSupportedLanguagesRequest;
use Google\Cloud\Translate\V3\TranslateTextRequest;
use Google\Cloud\Translate\V3\TranslateTextResponse;
use Plank\Polyglot\Contracts\AbstractTranslator;
use Plank\Polyglot\Exceptions\DriverException;

class GoogleV3Translate extends AbstractTranslator
{
    protected TranslationServiceClient $client;

    protected string $parent;

    protected string $model;

    /**
     * @throws ValidationException
     */
    public function __construct(string $projectId, array $options = [], string $format = 'html', string $model = 'nmt')
    {
        throw new DriverException('Google Cloud Translation API v3 is not yet supported.');
        $this->client = new TranslationServiceClient($options);
        $this->parent = TranslationServiceClient::locationName($projectId, 'global');
        $this->format = $format;
        $this->model = $model;
    }

    public function translate(string $text): string
    {
        $response = $this->sendTranslateRequest($text);

        $text = '';
        foreach ($response->getTranslations() as $translation) {
            $text .= $translation->getTranslatedText();
        }

        return $text;
    }

    public function translateBatch(array|string $strings): array
    {
        $response = $this->sendTranslateRequest($strings);

        return array_map(static fn ($item) => $item['text'], $response);
    }

    /**
     * @throws ApiException
     */
    public function sendTranslateRequest(string $text): TranslateTextResponse
    {
        $request = (new TranslateTextRequest)
            ->setContents([$text])
            ->setTargetLanguageCode($this->target)
            ->setParent($this->parent);

        if (isset($this->source)) {
            $request->setSourceLanguageCode($this->source);
        }

        if ($this->model !== 'nmt') {
            $request->setModel($this->model);
        }

        match ($this->format) {
            'text','plain' => $request->setMimeType('text/plain'),
            'html' => $request->setMimeType('text/html'),
            default => $request->setMimeType($this->format),
        };

        return $this->client->translateText($request);
    }

    public function languages($target = null): array
    {
        return $this->sendLanguagesRequest($target);
    }

    /**
     * @throws ApiException
     */
    public function sendLanguagesRequest($target = null): array
    {
        $request = (new GetSupportedLanguagesRequest)->setParent($this->parent);

        if ($target !== null) {
            $request->setDisplayLanguageCode($target);
        }

        $response = $this->client->getSupportedLanguages($request);

        $languages = [];
        foreach ($response->getLanguages() as $language) {
            if ($target !== null) {
                $languages[$language->getLanguageCode()] = $language->getDisplayName();
            } else {
                $languages[] = $language->getLanguageCode();
            }
        }

        return $languages;
    }
}
