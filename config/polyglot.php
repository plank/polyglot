<?php

return [
    'default' => env('POLYGLOT_DEFAULT', 'stack'),
    'translators' => [
        'stack' => [
            'driver' => 'stack',
            'translators' => ['google-free', 'amazon', 'google', 'gpt35'],
            'retries' => 3,
        ],
        'google-free' => [
            'driver' => 'stichoza',
        ],
        'google' => [
            'driver' => 'google',
            'key' => env('GOOGLE_TRANSLATE_API_KEY'),
            'format' => 'html',
            'model' => 'nmt',
        ],
        'google-v3' => [
            'driver' => 'google',
            'version' => 'v3',
            'project_id' => env('GOOGLE_TRANSLATE_PROJECT_ID'),
            'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
        ],
        'amazon' => [
            'driver' => 'amazon',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
        ],
        'gpt35' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
            'model' => 'gpt-3.5-turbo',
        ],
        'gpt4o' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'model' => 'gpt-4o',
        ],
        'gpt4' => [
            'driver' => 'openai',
            'key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'model' => 'gpt-4-turbo',
        ],
    ],
];
