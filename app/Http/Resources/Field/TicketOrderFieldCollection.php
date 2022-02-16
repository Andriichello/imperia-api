<?php

namespace App\Http\Resources\Field;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class TicketOrderFieldCollection.
 */
class TicketOrderFieldCollection extends ResourceCollection
{
    public $collects = TicketOrderFieldResource::class;
}
