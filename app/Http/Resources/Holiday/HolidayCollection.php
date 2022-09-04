<?php

namespace App\Http\Resources\Holiday;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class HolidayCollection.
 */
class HolidayCollection extends ResourceCollection
{
    public $collects = HolidayResource::class;

    public function __construct($resource)
    {
        $collection = collect($resource)
            ->sortBy('closest_date');

        parent::__construct($collection->values());
    }
}
