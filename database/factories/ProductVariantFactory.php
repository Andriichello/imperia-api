<?php

namespace Database\Factories;

use App\Enums\WeightUnit;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ProductVariantFactory.
 *
 * @method ProductVariant|Collection create($attributes = [], ?Model $parent = null)
 */
class ProductVariantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = ProductVariant::class;

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
     * Indicate variant's product.
     *
     * @param Product $product
     *
     * @return static
     */
    public function withProduct(Product $product): static
    {
        return $this->state(
            function (array $attributes) use ($product) {
                $attributes['product_id'] = $product->id;
                return $attributes;
            }
        );
    }
}
