<?php

namespace App\Http\Resources\Field;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class ServiceOrderFieldCollection.
 */
class ServiceOrderFieldCollection extends ResourceCollection
{
    public $collects = ServiceOrderFieldResource::class;
}
