<?php

// Published with: php artisan vendor:publish --tag=besolutions-config

return [
    // Base URL of your Be Solutions server.
    'base_url' => env('BES_BASE_URL', 'https://be-solutions.live'),

    // A "bes_…" API key (Dashboard → your project → API keys) …
    'api_key' => env('BES_API_KEY'),

    // … and/or the project key and secret: needed to sign access tokens locally and to verify webhooks.
    'project_key' => env('BES_PROJECT_KEY'),
    'project_secret' => env('BES_PROJECT_SECRET'),

    'timeout' => (int) env('BES_TIMEOUT', 20),
];
