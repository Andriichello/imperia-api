<?php

namespace Database\Factories;

use App\Models\Orders\TicketOrderField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class TicketOrderFieldFactory.
 *
 * @method TicketOrderField|Collection create($attributes = [], ?Model $parent = null)
 */
class TicketOrderFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = TicketOrderField::class;

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
}
