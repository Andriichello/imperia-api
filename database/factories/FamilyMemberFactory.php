<?php

namespace Database\Factories;

use App\Enums\FamilyRelation;
use App\Models\Customer;
use App\Models\FamilyMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class FamilyMemberFactory.
 *
 * @method FamilyMember|Collection create($attributes = [], ?Model $parent = null)
 */
class FamilyMemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = FamilyMember::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        /** @var Customer|null $customer */
        $customer = Customer::query()->inRandomOrder()->first();

        return [
            'name' => $this->faker->name,
            'relation' => FamilyRelation::Child,
            'birthdate' => $this->faker->dateTimeBetween('-8 years', '-3 years'),
            'relative_id' => $customer ? $customer->id : Customer::factory(),
        ];
    }

    /**
     * Indicate relative and relation for the family member.
     *
     * @param Customer $relative
     * @param FamilyRelation $relation
     *
     * @return static
     */
    public function withRelative(Customer $relative, FamilyRelation $relation): static
    {
        return $this->state(
            function (array $attributes) use ($relative, $relation) {
                $attributes['relative_id'] = $relative->id;
                $attributes['relation'] = $relation->value;
                return $attributes;
            }
        );
    }
}
