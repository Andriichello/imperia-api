<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class TicketFactory.
 *
 * @method Ticket|Collection create($attributes = [], ?Model $parent = null)
 */
class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique->title,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 20, 100),
        ];
    }
}
