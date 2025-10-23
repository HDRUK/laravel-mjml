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
            'mjmlRenderUrl' => env('MJML_RENDER_URL'),
        ],
    ],
    'email' => [
        'from_address' => env('MAIL_FROM_ADDRESS'),
    ],
];