<?php

return [
    'disk' => env('FILESYSTEM_MEDIA', 'public'),

    'folder' => env('MEDIA_FOLDER', '/media')
        . env('MEDIA_SUBFOLDER', '/uploaded/'),

    'max_size' => 10240, // kilobytes
];
