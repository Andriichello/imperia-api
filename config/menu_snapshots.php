<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Snapshots of Dishes
    |--------------------------------------------------------------------------
    |
    | The dishes guests see on a restaurant's pages are kept as gzipped JSON files
    | named after their content (see `MenuSnapshotRepository`): they're built once
    | after something changes, not on every page view.
    |
    */

    // where the files are kept: the public bucket of the media
    'disk' => env('MENU_SNAPSHOTS_DISK', env('FILESYSTEM_MEDIA', 'public')),

    // pages load the files from the bucket itself, not through the API (the bucket has
    // to let the site's pages read them: a CORS rule of GET for the site's domains)
    'direct' => (bool) env('MENU_SNAPSHOTS_DIRECT', false),

    // files, which aren't current anymore, stay this long for pages loaded before a change
    'keep_hours' => 24,

];
