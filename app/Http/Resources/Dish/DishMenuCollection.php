<?php

namespace App\Http\Resources\Dish;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class DishMenuCollection.
 */
class DishMenuCollection extends ResourceCollection
{
    public $collects = DishMenuResource::class;
}
