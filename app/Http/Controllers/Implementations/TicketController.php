<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Models\Ticket;

class TicketController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = Ticket::class;
}
