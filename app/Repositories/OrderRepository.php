<?php

namespace App\Repositories;

use App\Jobs\Order\CalculateTotals;
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
            CalculateTotals::dispatchSync($order);

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
            CalculateTotals::dispatchSync($model);

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
                /** @var SpaceOrderField $field */
                $field = $order->spaces()
                    ->updateOrCreate($identifiers, Arr::except($values, 'space_id'));

                $this->updateComments($field, $values);
                $this->updateDiscounts($field, $values);
            }

            $order->spaces()
                ->whereNotIn('space_id', Arr::pluck($attributes['spaces'], 'space_id'))
                ->forceDelete();
        }

        if (Arr::has($attributes, 'tickets')) {
            foreach ($attributes['tickets'] as $values) {
                $identifiers = Arr::only($values, 'ticket_id');
                /** @var TicketOrderField $field */
                $field = $order->tickets()
                    ->updateOrCreate($identifiers, Arr::except($values, 'ticket_id'));

                $this->updateComments($field, $values);
                $this->updateDiscounts($field, $values);
            }

            $order->tickets()
                ->whereNotIn('ticket_id', Arr::pluck($attributes['tickets'], 'ticket_id'))
                ->forceDelete();
        }

        if (Arr::has($attributes, 'products')) {
            $updated = [];

            foreach ($attributes['products'] as $values) {
                $identifiers = Arr::only($values, ['product_id', 'variant_id']);
                $identifiers['batch'] = data_get($values, 'batch');

                /** @var ProductOrderField $field */
                $field = $order->products()
                    ->updateOrCreate($identifiers, Arr::except($values, 'product_id'));

                $this->updateComments($field, $values);
                $this->updateDiscounts($field, $values);

                $updated[] = $field->id;
            }

            $order->products()
                ->whereNotIn('id', $updated)
                ->forceDelete();
        }

        if (Arr::has($attributes, 'services')) {
            foreach ($attributes['services'] as $values) {
                $identifiers = Arr::only($values, 'service_id');
                /** @var ServiceOrderField $field */
                $field = $order->services()
                    ->updateOrCreate($identifiers, Arr::except($values, 'service_id'));

                $this->updateComments($field, $values);
                $this->updateDiscounts($field, $values);
            }

            $order->services()
                ->whereNotIn('service_id', Arr::pluck($attributes['services'], 'service_id'))
                ->forceDelete();
        }

        return true;
    }
}
