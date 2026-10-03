<?php

namespace Tests\Http\Controllers\Editor;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\RestaurantReview;
use Laravel\Sanctum\Sanctum;

/**
 * Class ReviewEditorTest.
 *
 * Moderation of the reviews guests leave: the restaurant's admins approve or reject them.
 */
class ReviewEditorTest extends EditorTestCase
{
    /**
     * A review of the restaurant, which waits for approval.
     *
     * @param array $attributes
     *
     * @return RestaurantReview
     */
    protected function review(array $attributes = []): RestaurantReview
    {
        return RestaurantReview::factory()->withRestaurant($this->restaurant)->create($attributes);
    }

    /**
     * Test that the reviews, which wait for approval, are listed by default with the counts by status.
     *
     * @return void
     */
    public function testReviewsAreListedByStatus()
    {
        $first = $this->review(['created_at' => now()->subDay()]);
        $second = $this->review();
        RestaurantReview::factory()->withRestaurant($this->restaurant)->approved()->count(2)->create();
        RestaurantReview::factory()->withRestaurant($this->restaurant)->rejected()->create();
        RestaurantReview::factory()->withRestaurant(Restaurant::factory()->create())->create();

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}/reviews")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $second->id)
            ->assertJsonPath('data.1.id', $first->id)
            ->assertJsonPath('data.0.status', 'pending')
            ->assertJsonPath('counts', ['pending' => 2, 'approved' => 2, 'rejected' => 1])
            ->assertJsonMissingPath('data.0.ip_hash');

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}/reviews?status=approved")
            ->assertJsonCount(2, 'data');

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}/reviews?status=spam")
            ->assertUnprocessable();
    }

    /**
     * Test that an approved review is public, a rejected one isn't, and either can be decided again.
     *
     * @return void
     */
    public function testReviewsAreApprovedAndRejected()
    {
        $review = $this->review(['rating' => 2]);
        $public = fn () => $this->getJson("/api/restaurants/{$this->restaurant->id}/reviews")->json('summary.count');

        $this->postJson("/api/editor/reviews/{$review->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.moderated_by', ['id' => $this->admin->id, 'name' => $this->admin->name]);

        $this->assertNotNull($review->refresh()->moderated_at);
        $this->assertSame(1, $public());

        $this->postJson("/api/editor/reviews/{$review->id}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertSame(0, $public());
    }

    /**
     * Test that only the restaurant's admins moderate its reviews.
     *
     * @return void
     */
    public function testOnlyTheRestaurantsAdminsModerateReviews()
    {
        $review = $this->review();

        Sanctum::actingAs($this->user(UserRole::Admin, Restaurant::factory()->create()), ['*']);

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}/reviews")->assertForbidden();
        $this->postJson("/api/editor/reviews/{$review->id}/approve")->assertForbidden();

        Sanctum::actingAs($this->user(UserRole::Manager, $this->restaurant), ['*']);

        $this->postJson("/api/editor/reviews/{$review->id}/reject")->assertForbidden();
        $this->postJson('/api/editor/reviews/999/approve')->assertNotFound();

        $this->assertSame(RestaurantReview::STATUS_PENDING, $review->refresh()->status);
    }
}
