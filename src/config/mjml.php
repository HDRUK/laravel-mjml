<?php

return [
    /*
    |----------------------------------------------------------------------------------------------------
    | Default mjml config
    |----------------------------------------------------------------------------------------------------
    |
    | These options determine the current MJML configuration
    |
    */
    'default' => [
        'access' => [
            'mjmlApiApplicationKey' => env('MJML_API_APPLICATION_KEY'),
            'mjmlApiKey' => env('MJML_API_KEY'),
            'mjmlRenderUrl' => env('MJML_RENDER_URL'),
        ],
    ],
    'email' => [
        'from_address' => env('EMAIL_FROM_ADDRESS'),
    ],
];