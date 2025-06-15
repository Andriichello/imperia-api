<?php

namespace App\Http\Resources\Dish;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class DishVariantCollection.
 */
class DishVariantCollection extends ResourceCollection
{
    public $collects = DishVariantResource::class;
}
