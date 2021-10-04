<?php

return [
    'disks' => [
        'yandex-disk' => [
            'driver' => 'yandex-disk',
            'token' => env('YANDEX_DISK_OAUTH_TOKEN'),
            'prefix' => '/'.env('YANDEX_DISK_BASE_PATH', 'storage/'),
        ],
    ],
];
