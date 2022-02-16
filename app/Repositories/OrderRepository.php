<?php

namespace App\Repositories;

use App\Models\Orders\Order;
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

            $relations = Arr::only($attributes, ['spaces', 'tickets', 'services', 'products']);
            foreach ($relations as $relation => $fields) {
                foreach ($fields as $field) {
                    if ($relation === 'spaces') {
                        $startAt = data_get($field, 'start_at', $order->banquet->start_at);
                        $endAt = data_get($field, 'end_at', $order->banquet->end_at);

                        data_set($field, 'start_at', $startAt);
                        data_set($field, 'end_at', $endAt);
                    }

                    $order->$relation()->create($field);
                }
            }
            return $order->fresh();
        });
    }

    public function update(Order|Model $model, array $attributes): bool
    {
        return DB::transaction(function () use ($model, $attributes) {
            if (!parent::update($model, $attributes)) {
                return false;
            }

            $relations = Arr::only($attributes, ['spaces', 'tickets', 'services', 'products']);
            foreach ($relations as $relation => $fields) {
                foreach ($fields as $field) {
                    $model->$relation()->updateOrCreate($field);
                }
            }
            return $model->fresh();
        });
    }
}
