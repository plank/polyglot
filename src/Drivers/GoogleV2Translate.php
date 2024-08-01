<?php

namespace Plank\Polyglot\Drivers;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Translate\V2\TranslateClient;
use Plank\Polyglot\Contracts\AbstractTranslator;
use Plank\Polyglot\Exceptions\ValidationException;

class GoogleV2Translate extends AbstractTranslator
{
    protected TranslateClient $client;

    protected string $model;

    /**
     * @throws GoogleException
     */
    public function __construct(string $key, string $format = 'html', string $model = 'nmt')
    {
        $this->client = new TranslateClient(['key' => $key]);
        $this->format = $format;
        $this->model = $model;
    }

    public function translate(string $text): string
    {
        if (strlen($text) > 102400) {
            $strings = str_split($text, 102400);

            return implode('', $this->translateBatch($strings));
        }

        $response = $this->sendTranslateRequest($text, [
            'target' => $this->target,
            'source' => $this->source,
            'format' => $this->format,
            'model' => $this->model,
        ]);

        return $response['text'];
    }

    public function translateBatch(array $strings): array
    {
        $response = $this->sendTranslateRequest($strings, [
            'target' => $this->target,
            'source' => $this->source,
            'format' => $this->format,
            'model' => $this->model,
        ]);

        return array_map(static fn ($item) => $item['text'], $response);
    }

    /**
     * @throws ServiceException
     */
    public function sendTranslateRequest(string|array $text, array $options = []): array
    {
        if ($options['target'] === null) {
            throw new ValidationException('Google translation requires target locale.');
        }

        $options['source'] = $options['source'] ?? 'auto';
        $options['format'] = $options['format'] ?? 'html';
        $options['model'] = $options['model'] ?? 'nmt';

        if (is_array($text)) {
            return $this->client->translateBatch($text, $options);
        }

        return $this->client->translate($text, $options);
    }

    /**
     * @throws ServiceException
     */
    public function languages($target = null): array
    {
        if ($target !== null) {
            $options = ['target' => $target];

            return $this->client->localizedLanguages($options);
        }

        return $this->client->languages();
    }
}
