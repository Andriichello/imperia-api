<?php

namespace Database\Factories\Morphs;

use App\Models\Morphs\Discount;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class DiscountFactory.
 *
 * @method Discount|Collection create($attributes = [], ?Model $parent = null)
 */
class DiscountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Discount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $isAmount = $this->faker->boolean;
        $isPercent = $this->faker->boolean;
        return [
            'title' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->sentence(),
            'amount' => $isAmount ? $this->faker->randomFloat(2, 10, 1000) : null,
            'percent' => $isPercent ? $this->faker->randomFloat(2, 10, 90) : null,
        ];
    }

    /**
     * Indicate discount percent.
     *
     * @param float $amount
     *
     * @return static
     * @throws Exception
     */
    public function withAmount(float $amount): static
    {
        if ($amount <= 0) {
            throw new Exception('Discount amount should be in range greater then 0');
        }

        return $this->state(
            function (array $attributes) use ($amount) {
                $attributes['amount'] = $amount;
                return $attributes;
            }
        );
    }

    /**
     * Indicate discount percent.
     *
     * @param float $percent
     *
     * @return static
     * @throws Exception
     */
    public function withPercent(float $percent): static
    {
        if ($percent <= 0 || $percent > 100) {
            throw new Exception('Discount percent should be in range [0 ; 100]');
        }

        return $this->state(
            function (array $attributes) use ($percent) {
                $attributes['percent'] = $percent;
                return $attributes;
            }
        );
    }
}
