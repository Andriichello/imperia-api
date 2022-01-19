<?php

declare(strict_types=1);

return [

    // Manage autoload migrations
    'autoload_migrations' => false,

    // Categories Database Tables
    'tables' => [
        'categories' => 'morph_categories',
        'categorizables' => 'morph_categorizables',
    ],

    // Categories Models
    'models' => [
        'category' => \Rinvex\Categories\Models\Category::class,
    ],

];
