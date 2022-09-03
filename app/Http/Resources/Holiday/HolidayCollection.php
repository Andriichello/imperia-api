<?php

namespace App\Http\Resources\Holiday;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class HolidayCollection.
 */
class HolidayCollection extends ResourceCollection
{
    public $collects = HolidayResource::class;
}
