<?php

namespace App\Http\Resources\Schedule;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class ScheduleCollection.
 */
class ScheduleCollection extends ResourceCollection
{
    public $collects = ScheduleResource::class;

    public function __construct($resource)
    {
        $collection = collect($resource)
            ->sortBy('begs_in');

        parent::__construct($collection->values());
    }
}
