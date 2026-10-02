<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;

/**
 * Class LoginPageTest.
 */
class LoginPageTest extends FilamentTestCase
{
    /**
     * Test that a logged-in user, who opens the login page, is sent to the panel.
     *
     * @return void
     */
    public function testLoggedInUserIsRedirectedFromTheLoginPage()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(UserRole::Admin);

        /** @var SessionGuard $guard */
        $guard = Auth::guard('web');

        // logged in through the session, like in a browser (`actingAs()` would also make `web` the default guard)
        $this->withSession([$guard->getName() => $user->id])
            ->get(route('filament.admin.auth.login'))
            ->assertRedirect(route('filament.admin.pages.dashboard'));
    }
}
