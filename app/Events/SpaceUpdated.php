<?php

namespace App\Events;

use App\Models\Space;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpaceUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Space $space;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Space $space)
    {
        $this->space = $space;
    }
}
