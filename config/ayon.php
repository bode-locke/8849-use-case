<?php

return [
    'api_url' => env('AYON_API_URL', 'http://ayon:5000'),
    'api_key' => env('AYON_API_KEY'),
    'on_delete' => env('AYON_ON_DELETE', 'deactivate'),
    'queue' => env('AYON_QUEUE', 'default'),
];
