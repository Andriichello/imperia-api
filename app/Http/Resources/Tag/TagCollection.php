<?php

namespace App\Http\Resources\Tag;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class TagCollection.
 */
class TagCollection extends ResourceCollection
{
    public $collects = TagResource::class;
}
