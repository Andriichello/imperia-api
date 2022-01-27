<?php

namespace Database\Factories;

use App\Models\Banquet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class BanquetFactory.
 *
 * @method Banquet|Collection create($attributes = [], ?Model $parent = null)
 */
class BanquetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Banquet::class;

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
