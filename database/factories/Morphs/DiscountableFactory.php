<?php

namespace Database\Factories\Morphs;

use App\Models\BaseModel;
use App\Models\Morphs\Discount;
use App\Models\Morphs\Discountable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class DiscountableFactory.
 *
 * @method Discountable|Collection create($attributes = [], ?Model $parent = null)
 */
class DiscountableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Discountable::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'discount_id' => Discount::factory()
        ];
    }

    /**
     * Indicate discount.
     *
     * @param Discount $discount
     *
     * @return static
     */
    public function withDiscount(Discount $discount): static
    {
        return $this->state(
            function (array $attributes) use ($discount) {
                $attributes['discount_id'] = $discount->id;
                return $attributes;
            }
        );
    }

    /**
     * Indicate discountable model.
     *
     * @param BaseModel $model
     *
     * @return static
     */
    public function withModel(BaseModel $model): static
    {
        return $this->state(
            function (array $attributes) use ($model) {
                $attributes['discountable_id'] = $model->id;
                $attributes['discountable_type'] = $model->type;
                return $attributes;
            }
        );
    }
}
