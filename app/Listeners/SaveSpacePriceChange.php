<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use App\Events\SpaceCreated;
use App\Events\SpaceUpdated;
use App\Events\TicketCreated;
use App\Events\TicketUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveSpacePriceChange
{
    /**
     * Handle the event.
     *
     * @param  SpaceCreated|SpaceUpdated  $event
     * @return void
     */
    public function handle(SpaceCreated|SpaceUpdated $event)
    {
        if (!$this->shouldPriceBeSaved($event)) {
            return;
        }

        $success = DB::table('spaces_change_log')
            ->insert([
                'space_id' => $event->space->id,
                'price' => $event->space->price,
            ]);

        Log::info("SaveSpacePriceChange: " . ($success ? "SUCCESS" : "FAIL"));
    }

    protected function shouldPriceBeSaved(SpaceCreated|SpaceUpdated $event): bool {
        if ($event instanceof SpaceCreated) {
            return true;
        }

        return $event->space->getOriginal(['price']) != $event->space->price;
    }
}
