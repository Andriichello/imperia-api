<?php

namespace Tests\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Admin\SignInController;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Class SignInTest.
 *
 * Signing in to the restaurant admin (and the admin panel): invite-only, staff only.
 */
class SignInTest extends TestCase
{
    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // the page's scripts and styles aren't built for tests
        $this->withoutMix();
    }

    /**
     * The guard of the admin's session.
     *
     * @return SessionGuard
     */
    protected function guard(): SessionGuard
    {
        /** @var SessionGuard $guard */
        $guard = Auth::guard('web');

        return $guard;
    }

    /**
     * A user with the role and password.
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
            ->create(['email' => "$role@smak.ua", 'password' => 'menu-2026']);
    }

    /**
     * Sign in with the email and password.
     *
     * @param string $email
     * @param string $password
     * @param bool $remember
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function signIn(string $email, string $password, bool $remember = false): mixed
    {
        return $this->post(route('admin.login.store'), [
            'email' => $email,
            'password' => $password,
            'remember' => $remember ? '1' : '0',
        ]);
    }

    /**
     * Test that the page has what the form needs.
     *
     * @return void
     */
    public function testPageHasTheForm()
    {
        $this->get(route('admin.login', ['email' => 'anna@smak.ua']))
            ->assertOk()
            ->assertViewIs('admin.app')
            ->assertViewHas('page', 'sign-in')
            ->assertViewHas('props', fn (array $props) => $props['email'] === 'anna@smak.ua'
                && $props['remember'] === true
                && $props['error'] === null
                && $props['urls']['submit'] === route('admin.login.store'));
    }

    /**
     * Test that an admin of a restaurant signs in and goes to the dashboard, or where they wanted to.
     *
     * @return void
     */
    public function testAdminSignsIn()
    {
        $admin = $this->user(UserRole::Admin, Restaurant::factory()->create());

        $this->signIn($admin->email, 'menu-2026')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'web');

        $this->guard()->logout();

        $this->get(route('admin.editor.index'))->assertRedirect(route('admin.login'));

        $this->signIn($admin->email, 'menu-2026')
            ->assertRedirect(route('admin.editor.index'));
    }

    /**
     * Test that "keep me signed in" remembers the user on the device.
     *
     * @return void
     */
    public function testUserIsRememberedWhenAsked()
    {
        $admin = $this->user(UserRole::Admin, Restaurant::factory()->create());

        $this->signIn($admin->email, 'menu-2026', true)
            ->assertCookie($this->guard()->getRecallerName());

        $this->assertNotNull($admin->fresh()->getRememberToken());
    }

    /**
     * Test that a wrong password sends back to the page with the reason and the email.
     *
     * @return void
     */
    public function testWrongPasswordIsRejected()
    {
        $admin = $this->user(UserRole::Admin, Restaurant::factory()->create());

        $this->from(route('admin.login'))
            ->signIn($admin->email, 'wrong')
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('web');

        $this->get(route('admin.login'))
            ->assertViewHas('props', fn (array $props) => $props['error'] === ['code' => 'credentials']
                && $props['email'] === $admin->email);

        $this->signIn('', '')->assertSessionHasErrors(['email', 'password']);
    }

    /**
     * Test that wrong passwords in a row make signing in with the email wait.
     *
     * @return void
     */
    public function testAttemptsAreLimited()
    {
        $admin = $this->user(UserRole::Admin, Restaurant::factory()->create());

        for ($attempt = 0; $attempt < SignInController::MAX_ATTEMPTS; $attempt++) {
            $this->signIn($admin->email, 'wrong');
        }

        // even the right password waits
        $this->signIn($admin->email, 'menu-2026')
            ->assertSessionHas('sign_in_error', fn (array $error) => $error['code'] === 'throttled'
                && $error['seconds'] > 0);

        $this->assertGuest('web');
    }

    /**
     * Test that staff, who can't edit a restaurant, go to the admin panel, customers can't sign in.
     *
     * @return void
     */
    public function testOnlyStaffSignIn()
    {
        $manager = $this->user(UserRole::Manager, Restaurant::factory()->create());

        $this->signIn($manager->email, 'menu-2026')
            ->assertRedirect(route('filament.admin.pages.dashboard'));

        $this->guard()->logout();

        $customer = $this->user(UserRole::Customer);

        $this->signIn($customer->email, 'menu-2026')
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('sign_in_error', ['code' => 'not_allowed']);

        $this->assertGuest('web');
    }

    /**
     * Test that signing out ends the session.
     *
     * @return void
     */
    public function testUserSignsOut()
    {
        $admin = $this->user(UserRole::Admin, Restaurant::factory()->create());

        $this->signIn($admin->email, 'menu-2026');

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('web');
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }
}
