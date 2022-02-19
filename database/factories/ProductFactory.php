<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ProductFactory.
 *
 * @method Product|Collection create($attributes = [], ?Model $parent = null)
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Product::class;

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
            'menu_id' => Menu::factory(),
        ];
    }

    /**
     * Indicate related menu.
     *
     * @param Menu $menu
     *
     * @return static
     */
    public function withMenu(Menu $menu): static
    {
        return $this->state(
            function (array $attributes) use ($menu) {
                $attributes['menu_id'] = $menu->id;
                return $attributes;
            }
        );
    }
}
