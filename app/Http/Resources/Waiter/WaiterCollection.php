<?php

namespace App\Http\Resources\Waiter;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class WaiterCollection.
 */
class WaiterCollection extends ResourceCollection
{
    public $collects = WaiterResource::class;
}
