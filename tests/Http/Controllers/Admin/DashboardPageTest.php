<?php

namespace Tests\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Tests\TestCase;

/**
 * Class DashboardPageTest.
 *
 * The admin's home: the dashboard of the current restaurant, which the session remembers.
 */
class DashboardPageTest extends TestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

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

        $this->restaurant = Restaurant::factory()->create(['name' => 'Smak', 'timezone' => 'Europe/Kyiv']);
    }

    /**
     * A user with the role (of the restaurant, if given). Users are created before signing in:
     * signing in makes the session's guard the default one, which roles aren't for.
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
            ->create(['name' => 'Anna Kovalenko']);
    }

    /**
     * Sign in as the user, instead of the one, who was signed in before.
     *
     * @param User $user
     *
     * @return static
     */
    protected function signIn(User $user): static
    {
        $this->flushSession();

        return $this->actingAs($user, 'web');
    }

    /**
     * Test that the restaurant's admin gets its dashboard with the pages of the admin.
     *
     * @return void
     */
    public function testAdminGetsTheDashboard()
    {
        Restaurant::factory()->create();
        $admin = $this->user(UserRole::Admin, $this->restaurant);
        $this->signIn($admin);

        $props = $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewIs('admin.app')
            ->assertViewHas('page', 'dashboard')
            ->assertSee('<title>Smak</title>', false)
            ->viewData('props');

        $this->assertSame($this->restaurant->id, $props['restaurant']->resolve()['id']);
        $this->assertSame('Europe/Kyiv', $props['restaurant']->resolve()['timezone']);
        $this->assertSame([$this->restaurant->id], $props['restaurants']->pluck('id')->all());
        $this->assertSame(['name' => 'Anna Kovalenko', 'email' => $admin->email], $props['user']);
        $this->assertSame(route('admin.editor', ['id' => $this->restaurant->id]), $props['urls']['editor']);
        $this->assertSame(route('admin.logout'), $props['urls']['logout']);
        $this->assertSame(route('filament.admin.pages.dashboard'), $props['urls']['panel']);
    }

    /**
     * Test that admins of all restaurants switch between them, and the session remembers the one picked
     * (the editor opens it too). A restaurant's admin stays at theirs.
     *
     * @return void
     */
    public function testRestaurantIsSwitched()
    {
        $other = Restaurant::factory()->create();
        $superAdmin = $this->user(UserRole::Admin);
        $admin = $this->user(UserRole::Admin, $this->restaurant);

        $this->signIn($superAdmin);

        $current = fn () => $this->get(route('admin.dashboard'))->viewData('props')['restaurant']->resolve()['id'];

        $this->assertSame($this->restaurant->id, $current());

        $this->get(route('admin.dashboard', ['restaurant' => $other->id]))->assertOk();

        $this->assertSame($other->id, $current());
        $this->get(route('admin.editor.index'))->assertRedirect(route('admin.editor', ['id' => $other->id]));

        // opening the editor of a restaurant makes it the current one
        $this->get(route('admin.editor', ['id' => $this->restaurant->id]))->assertOk();
        $this->assertSame($this->restaurant->id, $current());

        $this->signIn($admin);

        $this->assertSame($this->restaurant->id, $this->get(route('admin.dashboard', ['restaurant' => $other->id]))
            ->viewData('props')['restaurant']->resolve()['id']);
    }

    /**
     * Test that guests sign in first, staff, who can't edit a restaurant, go to the admin panel,
     * customers can't open it.
     *
     * @return void
     */
    public function testOnlyAdminsGetTheDashboard()
    {
        $manager = $this->user(UserRole::Manager, $this->restaurant);
        $customer = $this->user(UserRole::Customer);

        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));

        $this->signIn($manager)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('filament.admin.pages.dashboard'));

        $this->signIn($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
