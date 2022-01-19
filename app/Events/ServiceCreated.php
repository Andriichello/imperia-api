<?php

namespace App\Events;

use App\Models\Product;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Service $service;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
}
