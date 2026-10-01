<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class UserFactory.
 *
 * @method User|Collection create($attributes = [], ?Model $parent = null)
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $name = $this->faker->firstName;
        $surname = $this->faker->lastName;
        $email = "$name.$surname@email.com";

        return [
            'name' => "$name $surname",
            'email' => $email,
            'password' => 'pa$$w0rd',
            'remember_token' => Str::random(10),
            'email_verified_at' => now(),
            'metadata' => '{"isPreviewOnly": false}',
        ];
    }

    /**
     * Indicate role for the user.
     *
     * @param UserRole $role
     *
     * @return static
     */
    public function withRole(UserRole $role): static
    {
        return $this->state([])
            // @phpstan-ignore-next-line
            ->afterCreating(function (User $user) use ($role) {
                $user->assignRole($role->value);
            });
    }

    /**
     * Indicate user's restaurant.
     *
     * @param Restaurant|int|null $restaurant
     *
     * @return static
     */
    public function withRestaurant(Restaurant|int|null $restaurant): static
    {
        return $this->state(
            function (array $attributes) use ($restaurant) {
                $attributes['restaurant_id'] = is_int($restaurant)
                    ? $restaurant : $restaurant?->id;

                return $attributes;
            }
        );
    }
}
