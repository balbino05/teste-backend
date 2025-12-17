<?php

return [
    'authorize' => [
        'url' => env('AUTHORIZE_SERVICE_URL', 'https://util.devi.tools/api/v2/authorize'),
    ],
    'notify' => [
        'url' => env('NOTIFY_SERVICE_URL', 'https://util.devi.tools/api/v1/notify'),
    ],
];

