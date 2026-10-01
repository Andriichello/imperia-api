<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Tests\TestCase;

/**
 * Class FilamentTestCase.
 */
abstract class FilamentTestCase extends TestCase
{
    /**
     * Log in to the admin panel as a staff member.
     *
     * @param string $role
     * @param Restaurant|null $restaurant
     *
     * @return User
     */
    protected function actingAsStaff(string $role = UserRole::Admin, ?Restaurant $restaurant = null): User
    {
        /** @var User $user */
        $user = User::factory()->create(['restaurant_id' => $restaurant?->id]);
        $user->assignRole($role);

        $panel = filament()->getPanel('admin');

        $this->actingAs($user, $panel->getAuthGuard());
        filament()->setCurrentPanel($panel);

        return $user;
    }
}
