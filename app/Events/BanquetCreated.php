<?php

namespace App\Events;

use App\Models\Banquet;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BanquetCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Banquet $banquet;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Banquet $banquet)
    {
        $this->banquet = $banquet;
    }
}
