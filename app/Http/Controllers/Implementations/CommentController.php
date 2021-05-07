<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Models\Comment;

class CommentController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Model's primary keys.
     *
     * @var string[]
     */
    protected $primaryKeys = ['id', 'container_id', 'container_type', 'target_id', 'target_type'];
}
