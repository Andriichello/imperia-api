<?php

namespace App\Listeners;

use App\Events\ServiceCreated;
use App\Events\ServiceUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveServicePriceChange
{
    /**
     * Handle the event.
     *
     * @param  ServiceCreated|ServiceUpdated  $event
     * @return void
     */
    public function handle(ServiceCreated|ServiceUpdated $event)
    {
        if (!$this->shouldPriceBeSaved($event)) {
            return;
        }

        $success = DB::table('services_change_log')
            ->insert([
                'service_id' => $event->service->id,
                'once_paid_price' => $event->service->once_paid_price,
                'hourly_paid_price' => $event->service->hourly_paid_price,
            ]);

        Log::info("SaveServicePriceChange: " . ($success ? "SUCCESS" : "FAIL"));
    }

    protected function shouldPriceBeSaved(ServiceCreated|ServiceUpdated $event): bool {
        if ($event instanceof ServiceCreated) {
            return true;
        }

        $updated = $event->service;
        $original = $event->service->getOriginal();

        $updatedOnce = data_get($updated, 'once_paid_price');
        $originalOnce = data_get($original, 'once_paid_price');
        $updatedHourly = data_get($updated, 'hourly_paid_price');
        $originalHourly = data_get($original, 'hourly_paid_price');

        return $updatedOnce !== $originalOnce || $updatedHourly !== $originalHourly;
    }
}
