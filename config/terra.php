<?php

return [
    'endpoint'    => env('TERRA_API_URL'),
    'dev_id'      => env('TERRA_API_DEV_ID'),
    'api_key'     => env('TERRA_API_KEY'),
    'signing_key' => env('TERRA_API_SIGNING_KEY'),
    'available_providers' => [
        'APPLE',
        'CLUE',
        'COROS',
        'FITBIT',
        'FLO',
        'GARMIN',
        'GOOGLE',
        'MYFITNESSPAL',
        'OURA',
        'POLAR',
        'SAMSUNG',
        'STRAVA',
        'SUUNTO',
        'WHOOP'
    ]
];
