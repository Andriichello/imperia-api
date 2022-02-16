<?php

namespace App\Http\Resources\Field;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class ProductOrderFieldCollection.
 */
class ProductOrderFieldCollection extends ResourceCollection
{
    public $collects = ProductOrderFieldResource::class;
}
