<?php

namespace Database\Factories;

use App\Models\DishCategory;
use App\Models\DishMenu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class DishCategoryFactory.
 *
 * @method DishCategory|Collection create($attributes = [], ?Model $parent = null)
 */
class DishCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = DishCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug,
            'title' => $this->faker->unique()->sentence(2),
            'description' => $this->faker->sentence,
            'popularity' => rand(0, 100),
        ];
    }

    /**
     * Indicate category's menu.
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
}
