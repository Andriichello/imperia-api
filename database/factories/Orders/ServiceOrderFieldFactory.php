<?php

namespace Database\Factories\Orders;

use App\Models\Orders\ServiceOrderField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ServiceOrderFieldFactory.
 *
 * @method ServiceOrderField|Collection create($attributes = [], ?Model $parent = null)
 */
class ServiceOrderFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = ServiceOrderField::class;

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
