<?php

namespace App\Nova\Actions;

use App\Jobs\Order\CalculateTotals as CalculateTotalsJob;
use App\Models\Banquet;
use App\Models\Orders\Order;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class CalculateTotals extends Action
{
    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     *
     * @return void
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        foreach ($models as $model) {
            if ($model instanceof Banquet) {
                $model = $model->order;
            }

            if ($model instanceof Order) {
                dispatch(new CalculateTotalsJob($model));
            }
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(): array
    {
        return [];
    }
}
