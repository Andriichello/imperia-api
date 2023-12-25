<?php

namespace Database\Factories\Morphs;

use App\Models\BaseModel;
use App\Models\Morphs\Tip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class TipFactory.
 *
 * @method Tip|Collection create($attributes = [], ?Model $parent = null)
 */
class TipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Tip::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(1, 1000),
            'commission' => null,
            'note' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate tip note.
     *
     * @param string $note
     *
     * @return static
     */
    public function withNote(string $note): static
    {
        return $this->state(
            function (array $attributes) use ($note) {
                $attributes['note'] = $note;
                return $attributes;
            }
        );
    }

    /**
     * Indicate tippable model.
     *
     * @param BaseModel $model
     *
     * @return static
     */
    public function withModel(BaseModel $model): static
    {
        return $this->state(
            function (array $attributes) use ($model) {
                $attributes['tippable_id'] = $model->id;
                $attributes['tippable_type'] = $model->type;
                return $attributes;
            }
        );
    }
}
