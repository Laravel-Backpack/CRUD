<?php

use Backpack\CRUD\app\Mcp\Tools\SearchBackpackDocs;

return [
    'mcp' => [
        'tools' => [
            'include' => [
                SearchBackpackDocs::class,
            ],
        ],
    ],
];
