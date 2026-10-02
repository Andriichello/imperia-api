<?php

namespace Tests\Http\Controllers\Editor;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Class EditorTestCase.
 *
 * A restaurant (in English by default) and its admin, who is signed in.
 */
abstract class EditorTestCase extends TestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * Admin of the restaurant.
     *
     * @var User
     */
    protected User $admin;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create([
            'name' => 'Smak',
            'timezone' => 'Europe/Kyiv',
            'locale' => 'en',
        ]);

        $this->admin = $this->user(UserRole::Admin, $this->restaurant);

        Sanctum::actingAs($this->admin, ['*']);
    }

    /**
     * Create a user with the role (of the restaurant, if given).
     *
     * @param string $role
     * @param Restaurant|null $restaurant
     *
     * @return User
     */
    protected function user(string $role, ?Restaurant $restaurant = null): User
    {
        return User::factory()
            ->withRole(UserRole::fromValue($role))
            ->withRestaurant($restaurant)
            ->create();
    }
}
