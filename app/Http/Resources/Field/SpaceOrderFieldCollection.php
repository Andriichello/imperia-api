<?php

namespace App\Http\Resources\Field;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class SpaceOrderFieldCollection.
 */
class SpaceOrderFieldCollection extends ResourceCollection
{
    public $collects = SpaceOrderFieldResource::class;
}
