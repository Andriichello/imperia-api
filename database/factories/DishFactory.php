<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\DishCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class DishFactory.
 *
 * @method Dish|Collection create($attributes = [], ?Model $parent = null)
 */
class DishFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Dish::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->sentence(2),
            'description' => $this->faker->sentence(5),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'weight' => $this->faker->randomFloat(2, 100, 1000),
            'popularity' => rand(0, 100),
        ];
    }

    /**
     * Indicate dish's menu.
     *
     * @param DishMenu|int|null $menu
     *
     * @return static
     */
    public function withMenu(DishMenu|int|null $menu): static
    {
        return $this->state(
            function (array $attributes) use ($menu) {
                $attributes['menu_id'] = is_int($menu)
                    ? $menu : $menu?->id;

                return $attributes;
            }
        );
    }

    /**
     * Indicate dish's category.
     *
     * @param DishCategory|int|null $category
     *
     * @return static
     */
    public function withCategory(DishCategory|int|null $category): static
    {
        return $this->state(
            function (array $attributes) use ($category) {
                $attributes['category_id'] = is_int($category)
                    ? $category : $category?->id;

                return $attributes;
            }
        );
    }
}
