<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Events\ProductUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveProductPriceChange
{
    /**
     * Handle the event.
     *
     * @param  ProductCreated|ProductUpdated  $event
     * @return void
     */
    public function handle(ProductCreated|ProductUpdated $event)
    {
        if (!$this->shouldPriceBeSaved($event)) {
            return;
        }

        $success = DB::table('products_change_log')
            ->insert([
                'product_id' => $event->product->id,
                'price' => $event->product->price,
            ]);

        Log::info("SaveProductPriceChange: " . ($success ? "SUCCESS" : "FAIL"));
    }

    protected function shouldPriceBeSaved(ProductCreated|ProductUpdated $event): bool {
        if ($event instanceof ProductCreated) {
            return true;
        }

        return $event->product->getOriginal(['price']) != $event->product->price;
    }
}
