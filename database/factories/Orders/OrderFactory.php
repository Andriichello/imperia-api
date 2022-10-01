<?php

namespace Database\Factories\Orders;

use App\Enums\BanquetState;
use App\Models\Banquet;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class OrderFactory.
 *
 * @method Order|Collection create($attributes = [], ?Model $parent = null)
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Order::class;

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
     * Indicate banquet.
     *
     * @param Banquet|int $banquet
     *
     * @return static
     */
    public function withBanquet(Banquet|int $banquet): static
    {
        return $this->afterCreating(
            function (Order $model) use ($banquet) {
                $model->banquets()->attach(is_int($banquet) ? $banquet : $banquet->id);
            }
        );
    }
}
