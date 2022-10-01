<?php

namespace Database\Factories;

use App\Enums\BanquetState;
use App\Enums\UserRole;
use App\Models\Banquet;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class BanquetFactory.
 *
 * @method Banquet|Collection create($attributes = [], ?Model $parent = null)
 */
class BanquetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Banquet::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(2),
            'description' => $this->faker->sentence(4),
            'advance_amount' => 0,
            'start_at' => Carbon::tomorrow()->setHour(8),
            'end_at' => Carbon::tomorrow()->setHour(23),
            'state' => BanquetState::Draft,
            'creator_id' => User::factory()->withRole(UserRole::Admin()),
            'customer_id' => Customer::factory(),
        ];
    }

    /**
     * Indicate creator.
     *
     * @param User $user
     *
     * @return static
     */
    public function withCreator(User $user): static
    {
        return $this->state(
            function (array $attributes) use ($user) {
                $attributes['creator_id'] = $user->id;
                return $attributes;
            }
        );
    }

    /**
     * Indicate customer.
     *
     * @param Customer $customer
     *
     * @return static
     */
    public function withCustomer(Customer $customer): static
    {
        return $this->state(
            function (array $attributes) use ($customer) {
                $attributes['customer_id'] = $customer->id;
                return $attributes;
            }
        );
    }

    /**
     * Indicate banquet state.
     *
     * @param BanquetState|string $state
     *
     * @return static
     */
    public function withState(BanquetState|string $state): static
    {
        return $this->state(
            function (array $attributes) use ($state) {
                $attributes['state'] = is_string($state) ? $state : $state->value;
                return $attributes;
            }
        );
    }

    /**
     * Indicate order.
     *
     * @param Order|int $order
     *
     * @return static
     */
    public function withOrder(Order|int $order): static
    {
        return $this->afterCreating(
            function (Banquet $model) use ($order) {
                $model->orders()->attach(is_int($order) ? $order : $order->id);
            }
        );
    }
}
