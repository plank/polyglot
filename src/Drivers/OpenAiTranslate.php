<?php

namespace Plank\Polyglot\Drivers;

use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;
use Plank\Polyglot\Contracts\AbstractTranslator;
use Plank\Polyglot\Exceptions\ValidationException;

class OpenAiTranslate extends AbstractTranslator
{
    protected Client $client;

    protected string $model;

    protected string $format;

    public function __construct(Client $client, string $format = 'html', string $model = 'gpt-3.5-turbo')
    {
        $this->client = $client;
        $this->format = $format;
        $this->model = $model;
    }

    public function translate(string $string): string
    {
        $response = $this->sendTranslateRequest($string, [
            'source' => $this->source,
            'target' => $this->target,
            'format' => $this->format,
            'model' => $this->model,
        ]);

        return $response->choices[0]->message->content;
    }

    public function sendTranslateRequest($text, $options): CreateResponse
    {
        $model = $options['model'] ?? $this->model;
        $format = $options['format'] ?? $this->format;
        $source = $options['source'];
        $target = $options['target'];

        if ($source === null) {
            throw new ValidationException('OpenAI translation requires source locale.');
        }

        if ($target === null) {
            throw new ValidationException('OpenAI translation requires target locale.');
        }

        $prompt = match ($format) {
            'html' => "Translate the user supplied html doc from $source to $target but you must retain the html structure and only translate the text",
            default => "Translate the user supplied $format from $source to $target",
        };

        $response = $this->client->chat()->create([
            'model' => $model,
            'messages' => [['role' => 'system', 'content' => $prompt], ['role' => 'user', 'content' => $text]],
        ]);

        return $response;
    }

    public function languages($target = null): array
    {
        return [];
    }
}
