<?php

namespace App\Http\Resources\Space;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class SpaceCollection.
 */
class SpaceCollection extends ResourceCollection
{
    public $collects = SpaceResource::class;
}
