<?php

namespace App\Listeners;

use App\Events\DiscountCreated;
use App\Events\DiscountUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveDiscountValuesChange
{
    /**
     * Handle the event.
     *
     * @param  DiscountCreated|DiscountUpdated $event
     * @return void
     */
    public function handle(DiscountCreated|DiscountUpdated $event)
    {
        if (!$this->shouldValuesBeSaved($event)) {
            return;
        }

        $success = DB::table('discounts_change_log')
            ->insert([
                'discount_id' => $event->discount->id,
                'amount' => $event->discount->amount,
                'percent' => $event->discount->percent,
            ]);

        Log::info("SaveDiscountPrice: " . ($success ? "SUCCESS" : "FAIL"));
    }

    protected function shouldValuesBeSaved(DiscountCreated|DiscountUpdated $event): bool {
        if ($event instanceof DiscountCreated) {
            return true;
        }

        return $event->discount->getOriginal(['amount']) != $event->discount->amount ||
            $event->discount->getOriginal(['percent']) != $event->discount->percent;
    }
}
