<?php

namespace Database\Factories;

use App\Models\MenuVersion;
use App\Models\Restaurant;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class MenuVersionFactory.
 *
 * @extends Factory<MenuVersion>
 */
class MenuVersionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<MenuVersion>
     */
    protected $model = MenuVersion::class;

    /**
     * Define the model's default state: a draft.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(2, true)),
            'status' => MenuVersion::STATUS_DRAFT,
        ];
    }

    /**
     * Version of the restaurant.
     *
     * @param Restaurant|int $restaurant
     *
     * @return static
     */
    public function withRestaurant(Restaurant|int $restaurant): static
    {
        return $this->state(fn () => [
            'restaurant_id' => is_int($restaurant) ? $restaurant : $restaurant->id,
        ]);
    }

    /**
     * Version created by the user.
     *
     * @param User|null $user
     *
     * @return static
     */
    public function createdBy(?User $user): static
    {
        return $this->state(fn () => ['created_by' => $user?->id]);
    }

    /**
     * Version scheduled at the time (in a week, if not given).
     *
     * @param CarbonInterface|null $goesLiveAt
     *
     * @return static
     */
    public function scheduled(?CarbonInterface $goesLiveAt = null): static
    {
        return $this->state(fn () => [
            'status' => MenuVersion::STATUS_SCHEDULED,
            'goes_live_at' => $goesLiveAt ?? Carbon::now()->addWeek()->startOfMinute(),
        ]);
    }
}
