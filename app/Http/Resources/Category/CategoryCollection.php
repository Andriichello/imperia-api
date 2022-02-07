<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class SpaceCollection.
 */
class CategoryCollection extends ResourceCollection
{
    public $collects = CategoryResource::class;
}
