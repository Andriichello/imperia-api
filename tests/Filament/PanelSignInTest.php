<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;

/**
 * Class PanelSignInTest.
 *
 * The admin panel (at `/admin/manage`) has no sign-in page of its own: it uses the
 * restaurant admin's one, and the same session.
 */
class PanelSignInTest extends FilamentTestCase
{
    /**
     * Test that guests are sent to the admin's sign-in, which brings them back.
     *
     * @return void
     */
    public function testGuestsSignInAtTheAdminsPage()
    {
        // created first: the panel's requests switch the default guard to its one
        /** @var User $user */
        $user = User::factory()->create(['password' => 'secret-password']);
        $user->assignRole(UserRole::Admin);

        $this->get('/admin/manage/dishes')
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'secret-password'])
            ->assertRedirect('/admin/manage/dishes');

        $this->get('/admin/manage')->assertOk();
    }

    /**
     * Test that a signed-in user, who opens the sign-in page, goes to the dashboard
     * (the panel's own login page used to fail with the app's default guard).
     *
     * @return void
     */
    public function testSignedInUserIsRedirectedFromTheSignInPage()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(UserRole::Admin);

        /** @var SessionGuard $guard */
        $guard = Auth::guard('web');

        // signed in through the session, like in a browser (`actingAs()` would also make `web` the default guard)
        $this->withSession([$guard->getName() => $user->id])
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }
}
