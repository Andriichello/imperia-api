<?php

namespace App\Http\Resources\Comment;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class CommentCollection.
 */
class CommentCollection extends ResourceCollection
{
    public $collects = CommentResource::class;
}
