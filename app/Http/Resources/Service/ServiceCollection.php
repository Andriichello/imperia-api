<?php

namespace App\Http\Resources\Service;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class ServiceCollection.
 */
class ServiceCollection extends ResourceCollection
{
    public $collects = ServiceResource::class;
}
