<?php

namespace App\Http\Resources\Field;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class SpaceReservationCollection.
 */
class SpaceReservationCollection extends ResourceCollection
{
    public $collects = SpaceReservationResource::class;
}
