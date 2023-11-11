<?php

namespace App\Http\Resources\Restaurant;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class RestaurantReviewCollection.
 */
class RestaurantReviewCollection extends ResourceCollection
{
    public $collects = RestaurantReviewResource::class;
}
