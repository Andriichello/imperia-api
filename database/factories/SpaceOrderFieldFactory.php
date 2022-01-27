<?php

namespace Database\Factories;

use App\Models\Orders\SpaceOrderField;
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
}
