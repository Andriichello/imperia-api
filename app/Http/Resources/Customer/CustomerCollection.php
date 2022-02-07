<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class CustomerCollection.
 */
class CustomerCollection extends ResourceCollection
{
    public $collects = CustomerResource::class;
}
