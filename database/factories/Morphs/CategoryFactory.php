<?php

namespace Database\Factories\Morphs;

use App\Models\Morphs\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class CategoryFactory.
 *
 * @method Category|Collection create($attributes = [], ?Model $parent = null)
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug,
            'title' => $this->faker->unique()->sentence(3),
            'target' => null,
            'description' => $this->faker->sentence,
        ];
    }

    /**
     * Indicate category target.
     *
     * @param string|null $target
     *
     * @return static
     */
    public function withTarget(?string $target): static
    {
        return $this->state(
            function (array $attributes) use ($target) {
                $attributes['target'] = $target;
                return $attributes;
            }
        );
    }
}
