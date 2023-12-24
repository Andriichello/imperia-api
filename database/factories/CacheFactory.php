<?php

namespace Database\Factories;

use App\Models\Cache;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class CacheFactory.
 *
 * @method Cache|Collection create($attributes = [], ?Model $parent = null)
 */
class CacheFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Cache::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->word(),
            'value' => $this->faker->sentence(),
            'expiration' => 0,
        ];
    }
}
