<?php

namespace Tests\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Class PasswordResetTest.
 *
 * "Forgot password?": a link by email, which sets a new password.
 */
class PasswordResetTest extends TestCase
{
    /**
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

        // the page's scripts and styles aren't built for tests
        $this->withoutMix();

        $this->admin = User::factory()
            ->withRole(UserRole::Admin())
            ->withRestaurant(Restaurant::factory()->create())
            ->create(['email' => 'anna@smak.ua', 'password' => 'old-password']);
    }

    /**
     * Test that the link is emailed, pointing at the admin's page, and that the page doesn't
     * tell whether the email belongs to someone.
     *
     * @return void
     */
    public function testLinkIsEmailed()
    {
        Notification::fake();

        $this->get(route('admin.password.request', ['email' => 'anna@smak.ua']))
            ->assertOk()
            ->assertViewHas('page', 'forgot-password')
            ->assertViewHas('props', fn (array $props) => $props['email'] === 'anna@smak.ua');

        $this->post(route('admin.password.email'), ['email' => 'anna@smak.ua'])
            ->assertRedirect(route('admin.password.request'))
            ->assertSessionHas('status', 'link_sent');

        Notification::assertSentTo($this->admin, ResetPassword::class, function (ResetPassword $notification) {
            $url = $notification->toMail($this->admin)->actionUrl;

            return str_starts_with($url, route('admin.password.reset', ['token' => $notification->token]))
                && str_contains($url, 'email=anna%40smak.ua');
        });

        // the same answer for an unknown email
        $this->post(route('admin.password.email'), ['email' => 'nobody@smak.ua'])
            ->assertSessionHas('status', 'link_sent');

        // a second link right away waits
        $this->post(route('admin.password.email'), ['email' => 'anna@smak.ua'])
            ->assertSessionHas('password_error', 'throttled');
    }

    /**
     * Test that the link sets a new password, and the user signs in with it.
     *
     * @return void
     */
    public function testPasswordIsReset()
    {
        /** @var PasswordBroker $broker */
        $broker = Password::broker();
        $token = $broker->createToken($this->admin);

        $this->get(route('admin.password.reset', ['token' => $token, 'email' => 'anna@smak.ua']))
            ->assertOk()
            ->assertViewHas('page', 'reset-password')
            ->assertViewHas('props', fn (array $props) => $props['token'] === $token
                && $props['email'] === 'anna@smak.ua');

        $this->post(route('admin.password.update'), [
            'token' => $token,
            'email' => 'anna@smak.ua',
            'password' => 'new-password',
            'password_confirmation' => 'other-password',
        ])->assertSessionHasErrors(['password']);

        $this->post(route('admin.password.update'), [
            'token' => $token,
            'email' => 'anna@smak.ua',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
            ->assertRedirect(route('admin.login', ['email' => 'anna@smak.ua']))
            ->assertSessionHas('status', 'password_reset');

        $this->assertTrue(Hash::check('new-password', $this->admin->fresh()->password));

        // the link works once
        $this->from(route('admin.password.reset', ['token' => $token]))
            ->post(route('admin.password.update'), [
                'token' => $token,
                'email' => 'anna@smak.ua',
                'password' => 'newer-password',
                'password_confirmation' => 'newer-password',
            ])
            ->assertSessionHas('password_error', 'invalid_link');

        $this->post(route('admin.login.store'), ['email' => 'anna@smak.ua', 'password' => 'new-password'])
            ->assertRedirect(route('admin.dashboard'));
    }
}
