<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\SpaceOrderField;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class SpaceOrderFieldFactory.
 *
 * @method SpaceOrderField|Collection create($attributes = [], ?Model $parent = null)
 */
class SpaceOrderFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = SpaceOrderField::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            //
        ];
    }

    /**
     * Indicate space.
     *
     * @param Space $space
     *
     * @return static
     */
    public function withSpace(Space $space): static
    {
        return $this->state(
            function (array $attributes) use ($space) {
                $attributes['space_id'] = $space->id;
                return $attributes;
            }
        );
    }

    /**
     * Indicate order.
     *
     * @param Order $order
     *
     * @return static
     */
    public function withOrder(Order $order): static
    {
        return $this->state(
            function (array $attributes) use ($order) {
                $attributes['order_id'] = $order->id;
                return $attributes;
            }
        );
    }
}
