<?php

namespace App\Repositories;

use App\Models\Orders\Order;
use App\Models\Orders\ProductOrderField;
use App\Models\Orders\ServiceOrderField;
use App\Models\Orders\SpaceOrderField;
use App\Models\Orders\TicketOrderField;
use App\Repositories\Traits\CommentableRepositoryTrait;
use App\Repositories\Traits\DiscountableRepositoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class OrderRepository.
 */
class OrderRepository extends CrudRepository
{
    use CommentableRepositoryTrait;
    use DiscountableRepositoryTrait;

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
            $this->createComments($order, $attributes);
            $this->createDiscounts($order, $attributes);

            return $order->fresh();
        });
    }

    public function update(Order|Model $model, array $attributes): bool
    {
        return DB::transaction(function () use ($model, $attributes) {
            /** @var Order $model */
            if (!parent::update($model, $attributes)) {
                return false;
            }
            $this->createOrUpdateRelations($model, $attributes);
            $this->updateComments($model, $attributes);
            $this->updateDiscounts($model, $attributes);

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
                /** @var SpaceOrderField $field */
                $field = $order->spaces()
                    ->updateOrCreate(Arr::only($values, 'space_id'), $values);

                $this->updateComments($field, $values);
                $this->updateDiscounts($field, $values);
            }

            $order->spaces()
                ->whereNotIn('space_id', Arr::pluck($attributes['spaces'], 'space_id'))
                ->delete();
        }

        if (Arr::has($attributes, 'tickets')) {
            foreach ($attributes['tickets'] as $values) {
                $identifiers = Arr::only($values, 'ticket_id');
                /** @var TicketOrderField $field */
                $field = $order->tickets()->updateOrCreate($identifiers, $values);

                $this->updateComments($field, $values);
                $this->updateDiscounts($field, $values);
            }

            $order->tickets()
                ->whereNotIn('ticket_id', Arr::pluck($attributes['tickets'], 'ticket_id'))
                ->delete();
        }

        if (Arr::has($attributes, 'products')) {
            foreach ($attributes['products'] as $values) {
                $identifiers = Arr::only($values, 'product_id');
                /** @var ProductOrderField $field */
                $field = $order->products()->updateOrCreate($identifiers, $values);

                $this->updateComments($field, $values);
                $this->updateDiscounts($field, $values);
            }

            $order->products()
                ->whereNotIn('product_id', Arr::pluck($attributes['products'], 'product_id'))
                ->delete();
        }

        if (Arr::has($attributes, 'services')) {
            foreach ($attributes['services'] as $values) {
                $identifiers = Arr::only($values, 'service_id');
                /** @var ServiceOrderField $field */
                $field = $order->services()->updateOrCreate($identifiers, $values);

                $this->updateComments($field, $values);
                $this->updateDiscounts($field, $values);
            }

            $order->services()
                ->whereNotIn('service_id', Arr::pluck($attributes['services'], 'service_id'))
                ->delete();
        }

        return true;
    }
}
