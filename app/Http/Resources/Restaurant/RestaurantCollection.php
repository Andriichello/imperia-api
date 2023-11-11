<?php

namespace App\Http\Resources\Restaurant;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class RestaurantCollection.
 */
class RestaurantCollection extends ResourceCollection
{
    public $collects = RestaurantResource::class;
}
