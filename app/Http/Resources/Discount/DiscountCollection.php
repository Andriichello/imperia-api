<?php

namespace App\Http\Resources\Discount;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class DiscountCollection.
 */
class DiscountCollection extends ResourceCollection
{
    public $collects = DiscountResource::class;
}
