<?php

namespace App\Repositories;

use App\Models\Orders\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class OrderRepository.
 */
class OrderRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Order::class;

    public function create(array $attributes): Model
    {
        return DB::transaction(function () use ($attributes) {
            /** @var Order $order */
            $order = parent::create($attributes);
            $this->createOrUpdateRelations($order, $attributes);

            return $order->fresh();
        });
    }

    public function update(Order|Model $model, array $attributes): bool
    {
        return DB::transaction(function () use ($model, $attributes) {
            if (!parent::update($model, $attributes)) {
                return false;
            }
            /** @var Order $model */
            $this->createOrUpdateRelations($model, $attributes);

            return true;
        });
    }

    /**
     * Create or update all relations given in the attributes array.
     * Relations may be `spaces`, `tickets`, `services`, `products`.
     *
     * @param Order $order
     * @param array $attributes
     *
     * @return bool
     */
    public function createOrUpdateRelations(Order $order, array $attributes): bool
    {
        if (Arr::has($attributes, 'spaces')) {
            foreach ($attributes['spaces'] as $values) {
                $identifiers = Arr::only($values, 'space_id');

                $startAt = data_get($values, 'start_at', $order->banquet->start_at);
                $endAt = data_get($values, 'end_at', $order->banquet->end_at);

                $order->spaces()->updateOrCreate($identifiers, array_merge(
                    $values,
                    [
                        'start_at' => Carbon::make($startAt),
                        'end_at' => Carbon::make($endAt),
                    ],
                ));
            }

            $order->spaces()
                ->whereNotIn('space_id', Arr::pluck($attributes['spaces'], 'space_id'))
                ->delete();
        }

        if (Arr::has($attributes, 'tickets')) {
            foreach ($attributes['tickets'] as $values) {
                $identifiers = Arr::only($values, 'ticket_id');
                $order->tickets()->updateOrCreate($identifiers, $values);
            }

            $order->tickets()
                ->whereNotIn('ticket_id', Arr::pluck($attributes['tickets'], 'ticket_id'))
                ->delete();
        }

        if (Arr::has($attributes, 'products')) {
            foreach ($attributes['products'] as $values) {
                $identifiers = Arr::only($values, 'product_id');
                $order->products()->updateOrCreate($identifiers, $values);
            }

            $order->products()
                ->whereNotIn('product_id', Arr::pluck($attributes['products'], 'product_id'))
                ->delete();
        }

        if (Arr::has($attributes, 'services')) {
            foreach ($attributes['services'] as $values) {
                $identifiers = Arr::only($values, 'service_id');
                $order->services()->updateOrCreate($identifiers, $values);
            }

            $order->services()
                ->whereNotIn('service_id', Arr::pluck($attributes['services'], 'service_id'))
                ->delete();
        }

        return true;
    }
}
