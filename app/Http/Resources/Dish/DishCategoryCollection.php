<?php

namespace App\Http\Resources\Dish;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class DishCategoryCollection.
 */
class DishCategoryCollection extends ResourceCollection
{
    public $collects = DishCategoryResource::class;
}
