<?php

namespace App\Http\Resources\Ticket;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class TicketCollection.
 */
class TicketCollection extends ResourceCollection
{
    public $collects = TicketResource::class;
}
