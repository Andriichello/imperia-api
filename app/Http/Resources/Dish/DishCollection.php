<?php

namespace App\Http\Resources\Dish;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class DishCollection.
 */
class DishCollection extends ResourceCollection
{
    public $collects = DishResource::class;
}
