<?php

namespace Database\Factories;

use App\Enums\WeightUnit;
use App\Models\Dish;
use App\Models\DishVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class DishVariantFactory.
 *
 * @method DishVariant|Collection create($attributes = [], ?Model $parent = null)
 */
class DishVariantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = DishVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'price' => $this->faker->randomFloat(2, 10, 100),
            'weight' => $this->faker->randomFloat(2, 100, 1000),
            'weight_unit' => WeightUnit::Gram,
        ];
    }

    /**
     * Indicate variant's dish.
     *
     * @param Dish $dish
     *
     * @return static
     */
    public function withDish(Dish $dish): static
    {
        return $this->state(
            function (array $attributes) use ($dish) {
                $attributes['dish_id'] = $dish->id;
                return $attributes;
            }
        );
    }
}
