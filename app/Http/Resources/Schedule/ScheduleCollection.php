<?php

namespace App\Http\Resources\Schedule;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class ScheduleCollection.
 */
class ScheduleCollection extends ResourceCollection
{
    public $collects = ScheduleResource::class;
}
