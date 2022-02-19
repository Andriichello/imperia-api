<?php

namespace Database\Factories\Orders;

use App\Models\Orders\ProductOrderField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ProductOrderFieldFactory.
 *
 * @method ProductOrderField|Collection create($attributes = [], ?Model $parent = null)
 */
class ProductOrderFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = ProductOrderField::class;

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
