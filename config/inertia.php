<?php

return [

    'ssr' => [
        'enabled' => true,
        'url' => 'http://127.0.0.1:13714',
    ],

    'ensure_pages_exist' => false,

    'page_paths' => [
        resource_path('js/pages'),
    ],

    'page_extensions' => ['tsx', 'ts'],

    'testing' => [
        'ensure_pages_exist' => true,
        'page_paths' => [
            resource_path('js/pages'),
        ],
        'page_extensions' => ['tsx', 'ts'],
    ],

    'history' => [
        'encrypt' => false,
    ],

];
