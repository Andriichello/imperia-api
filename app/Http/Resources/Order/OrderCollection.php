<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class OrderCollection.
 */
class OrderCollection extends ResourceCollection
{
    public $collects = OrderResource::class;
}
