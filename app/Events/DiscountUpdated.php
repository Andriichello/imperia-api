<?php

namespace App\Events;

use App\Models\Discount;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscountUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Discount $discount;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Discount $discount)
    {
        $this->discount = $discount;
    }
}
