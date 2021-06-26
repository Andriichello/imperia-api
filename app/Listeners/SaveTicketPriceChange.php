<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\Events\TicketCreated;
use App\Events\TicketUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveTicketPriceChange
{
    /**
     * Handle the event.
     *
     * @param  TicketCreated|TicketUpdated  $event
     * @return void
     */
    public function handle(TicketCreated|TicketUpdated $event)
    {
        if (!$this->shouldPriceBeSaved($event)) {
            return;
        }

        $success = DB::table('tickets_change_log')
            ->insert([
                'ticket_id' => $event->ticket->id,
                'price' => $event->ticket->price,
            ]);

        Log::info("SaveTicketPriceChange: " . ($success ? "SUCCESS" : "FAIL"));
    }

    protected function shouldPriceBeSaved(TicketCreated|TicketUpdated $event): bool {
        if ($event instanceof TicketCreated) {
            return true;
        }

        return $event->ticket->getOriginal(['price']) != $event->ticket->price;
    }
}
