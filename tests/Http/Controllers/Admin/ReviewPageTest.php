<?php

namespace Tests\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Models\RestaurantReview;
use App\Models\User;
use Tests\Http\Controllers\Editor\EditorTestCase;

/**
 * Class ReviewPageTest.
 *
 * The reviews of the current restaurant, a page of the restaurant admin, and the reviews on its
 * dashboard.
 */
class ReviewPageTest extends EditorTestCase
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
     * Test that the admin gets the reviews page of the current restaurant, with how many reviews wait.
     *
     * @return void
     */
    public function testAdminOpensTheReviews()
    {
        RestaurantReview::factory()->withRestaurant($this->restaurant)->count(2)->create();
        RestaurantReview::factory()->withRestaurant($this->restaurant)->approved()->create(['rating' => 4]);

        $props = $this->signIn($this->admin)
            ->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertViewIs('admin.app')
            ->assertViewHas('page', 'reviews')
            ->assertSee('<title>Reviews · ' . $this->restaurant->name . '</title>', false)
            ->viewData('props');

        $restaurant = $props['restaurant']->resolve();

        $this->assertSame($this->restaurant->id, $restaurant['id']);
        $this->assertSame(2, $restaurant['pending_reviews']);
        $this->assertSame(1, $restaurant['reviews']['count']);
        $this->assertEquals(4, $restaurant['reviews']['average']);
        $this->assertSame(route('admin.reviews.index'), $props['urls']['reviews']);
    }

    /**
     * Test that the dashboard has the reviews and the link to them.
     *
     * @return void
     */
    public function testDashboardHasTheReviews()
    {
        RestaurantReview::factory()->withRestaurant($this->restaurant)->create();

        $props = $this->signIn($this->admin)->get(route('admin.dashboard'))->viewData('props');

        $this->assertSame(1, $props['restaurant']->resolve()['pending_reviews']);
        $this->assertSame(0, $props['restaurant']->resolve()['reviews']['count']);
        $this->assertSame(route('admin.reviews.index'), $props['urls']['reviews']);
    }

    /**
     * Test that guests sign in first, staff without a restaurant are told so on the dashboard.
     *
     * @return void
     */
    public function testOnlyAdminsOpenTheReviews()
    {
        $manager = $this->user(UserRole::Manager, $this->restaurant);
        $customer = $this->user(UserRole::Customer);

        $this->get(route('admin.reviews.index'))->assertRedirect(route('admin.login'));

        $this->signIn($manager)->get(route('admin.reviews.index'))->assertRedirect(route('admin.dashboard'));
        $this->signIn($customer)->get(route('admin.reviews.index'))->assertRedirect(route('admin.dashboard'));
    }
}
