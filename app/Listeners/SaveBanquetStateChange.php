<?php

namespace App\Listeners;

use App\Events\BanquetCreated;
use App\Events\BanquetUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveBanquetStateChange
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  BanquetCreated|BanquetUpdated $event
     * @return void
     */
    public function handle(BanquetCreated|BanquetUpdated $event)
    {
        if (!$this->shouldStateBeSaved($event)) {
            return;
        }

        $success = DB::table('banquet_state_change_log')
            ->insert([
                'state_id' => $event->banquet->state_id,
                'banquet_id' => $event->banquet->id,
            ]);

        Log::info("SaveBanquetState: " . ($success ? "SUCCESS" : "FAIL"));
    }

    protected function shouldStateBeSaved(BanquetCreated|BanquetUpdated $event): bool {
        if ($event instanceof BanquetCreated) {
            return true;
        }

        return $event->banquet->getOriginal(['state_id']) != $event->banquet->state_id;
    }
}
