<?php

namespace Tests\Http\Controllers\Web;

use App\Models\Restaurant;
use App\Models\RestaurantReview;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Class ReviewControllerTest.
 *
 * Reviews of a restaurant on the public site: guests leave them, they're public once approved.
 */
class ReviewControllerTest extends TestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * Token of the guest's device.
     */
    protected const TOKEN = '6f1c2d3e-4b5a-4c6d-8e7f-9a0b1c2d3e4f';

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create();
    }

    /**
     * Leave a review of the restaurant.
     *
     * @param array $data
     * @param string $token of the device
     * @param string $ipAddress
     *
     * @return TestResponse
     */
    protected function leave(
        array $data = [],
        string $token = self::TOKEN,
        string $ipAddress = '127.0.0.1',
    ): TestResponse {
        return $this->withServerVariables(['REMOTE_ADDR' => $ipAddress])
            ->postJson("/api/restaurants/{$this->restaurant->id}/reviews", [
                'rating' => 5,
                'name' => 'Olena',
                'text' => "Best borscht I've had in Kyiv.",
                'locale' => 'en',
                'client_token' => $token,
                ...$data,
            ]);
    }

    /**
     * Test that a guest's review waits for approval: it's not public, neither IP nor device is in it.
     *
     * @return void
     */
    public function testReviewWaitsForApproval()
    {
        $this->leave(['name' => '', 'text' => null])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.name', null)
            ->assertJsonPath('data.rating', 5)
            ->assertJsonMissingPath('data.ip_hash')
            ->assertJsonMissingPath('data.client_hash');

        /** @var RestaurantReview $review */
        $review = RestaurantReview::query()->firstOrFail();

        $this->assertSame(RestaurantReview::hash('127.0.0.1'), $review->ip_hash);
        $this->assertSame(RestaurantReview::hash(self::TOKEN), $review->client_hash);
        $this->assertSame('en', $review->locale);

        $this->getJson("/api/restaurants/{$this->restaurant->id}/reviews")
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('summary.count', 0)
            ->assertJsonPath('summary.average', null);
    }

    /**
     * Test that a review needs a rating from 1 to 5, a name and a text are limited.
     *
     * @return void
     */
    public function testReviewIsValidated()
    {
        $this->leave(['rating' => null])->assertUnprocessable()->assertJsonValidationErrors('rating');
        $this->leave(['rating' => 6])->assertUnprocessable()->assertJsonValidationErrors('rating');
        $this->leave(['rating' => 0])->assertUnprocessable()->assertJsonValidationErrors('rating');
        $this->leave(['name' => str_repeat('a', 41)])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->leave(['text' => str_repeat('a', 1001)])->assertUnprocessable()->assertJsonValidationErrors('text');
        $this->leave(['locale' => 'de'])->assertUnprocessable()->assertJsonValidationErrors('locale');
        $this->leave([], 'short')->assertUnprocessable()->assertJsonValidationErrors('client_token');

        $this->postJson('/api/restaurants/999/reviews', ['rating' => 5])->assertNotFound();
        $this->assertSame(0, RestaurantReview::query()->count());
    }

    /**
     * Test that only approved reviews are public: in the list, the average, the count and the counts
     * by rating. Rejected ones never are, and other restaurants' ones aren't there.
     *
     * @return void
     */
    public function testOnlyApprovedReviewsArePublic()
    {
        $approved = fn (int $rating) => RestaurantReview::factory()->withRestaurant($this->restaurant)->approved()
            ->create(['rating' => $rating]);

        $approved(5);
        $approved(5);
        $approved(4);
        $approved(2);
        RestaurantReview::factory()->withRestaurant($this->restaurant)->create(['rating' => 1]);
        RestaurantReview::factory()->withRestaurant($this->restaurant)->rejected()->create(['rating' => 1]);
        RestaurantReview::factory()->withRestaurant(Restaurant::factory()->create())->approved()->create();

        $this->getJson("/api/restaurants/{$this->restaurant->id}/reviews")
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('meta.total', 4)
            ->assertJsonPath('summary.average', 4)
            ->assertJsonPath('summary.count', 4)
            ->assertJsonPath('summary.ratings', [
                ['rating' => 5, 'count' => 2],
                ['rating' => 4, 'count' => 1],
                ['rating' => 3, 'count' => 0],
                ['rating' => 2, 'count' => 1],
                ['rating' => 1, 'count' => 0],
            ]);

        $approved(4);

        // 20 / 5
        $this->getJson("/api/restaurants/{$this->restaurant->slug}/reviews")
            ->assertJsonPath('summary.average', 4)
            ->assertJsonPath('summary.count', 5);

        $approved(5);

        // 25 / 6 = 4.1666…
        $this->getJson("/api/restaurants/{$this->restaurant->id}/reviews")
            ->assertJsonPath('summary.average', 4.2);
    }

    /**
     * Test that the newest reviews come first, the highest or the lowest rated ones on request,
     * and a page shows ten of them.
     *
     * @return void
     */
    public function testReviewsAreSortedAndPaged()
    {
        foreach (range(1, 12) as $day) {
            RestaurantReview::factory()->withRestaurant($this->restaurant)->approved()->create([
                'rating' => $day % 5 + 1,
                'created_at' => Carbon::now()->subDays($day),
            ]);
        }

        $newest = $this->getJson("/api/restaurants/{$this->restaurant->id}/reviews")
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.last_page', 2)
            ->json('data');

        $dates = array_column($newest, 'created_at');
        $this->assertSame($dates, collect($dates)->sortDesc()->values()->all());

        $this->getJson("/api/restaurants/{$this->restaurant->id}/reviews?page=2")
            ->assertJsonCount(2, 'data');

        $highest = $this->getJson("/api/restaurants/{$this->restaurant->id}/reviews?sort=highest")->json('data');
        $this->assertSame(5, $highest[0]['rating']);

        $lowest = $this->getJson("/api/restaurants/{$this->restaurant->id}/reviews?sort=lowest")->json('data');
        $this->assertSame(1, $lowest[0]['rating']);

        $this->getJson("/api/restaurants/{$this->restaurant->id}/reviews?sort=random")->assertUnprocessable();
    }

    /**
     * Test that a device leaves one review of a restaurant a day.
     *
     * @return void
     */
    public function testDeviceLeavesOneReviewADay()
    {
        $this->leave()->assertCreated();
        $this->leave(['rating' => 1])->assertStatus(429);

        // another device, or another restaurant
        $this->leave([], 'another-device-token-0001')->assertCreated();
        $other = $this->restaurant;
        $this->restaurant = Restaurant::factory()->create();
        $this->leave()->assertCreated();
        $this->restaurant = $other;

        $this->travel(25)->hours();
        $this->leave()->assertCreated();

        $this->assertSame(4, RestaurantReview::query()->count());
    }

    /**
     * Test that an IP leaves 20 reviews an hour (guests in a restaurant may share its Wi-Fi).
     *
     * @return void
     */
    public function testIpLeavesTwentyReviewsAnHour()
    {
        foreach (range(1, 20) as $device) {
            $this->leave([], "device-token-of-guest-$device")->assertCreated();
        }

        $this->leave([], 'device-token-of-guest-21')->assertStatus(429);
        $this->leave([], 'device-token-of-guest-21', '10.0.0.2')->assertCreated();
    }

    /**
     * Test that a review, which fills in the field guests don't see, looks left, but isn't kept.
     *
     * @return void
     */
    public function testBotsReviewIsNotKept()
    {
        $this->leave(['website' => 'https://spam.example'])
            ->assertCreated()
            ->assertJsonPath('data.id', null)
            ->assertJsonPath('data.status', 'pending');

        $this->assertSame(0, RestaurantReview::query()->count());
    }

    /**
     * Test that a device sees its own reviews whatever their status, nobody else does.
     *
     * @return void
     */
    public function testDeviceSeesItsOwnReviews()
    {
        $id = $this->leave()->json('data.id');
        $other = RestaurantReview::factory()->withRestaurant($this->restaurant)->create();

        $this->postJson("/api/restaurants/{$this->restaurant->id}/reviews/mine", [
            'client_token' => self::TOKEN,
            'ids' => [$id, $other->id],
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $id)
            ->assertJsonPath('data.0.status', 'pending')
            ->assertJsonPath('data.0.text', "Best borscht I've had in Kyiv.");

        $this->postJson("/api/restaurants/{$this->restaurant->id}/reviews/mine", [
            'client_token' => 'somebody-elses-token-0001',
            'ids' => [$id],
        ])->assertOk()->assertJsonCount(0, 'data');

        RestaurantReview::query()->whereKey($id)->update(['status' => RestaurantReview::STATUS_REJECTED]);

        $this->postJson("/api/restaurants/{$this->restaurant->id}/reviews/mine", [
            'client_token' => self::TOKEN,
            'ids' => [$id],
        ])->assertJsonPath('data.0.status', 'rejected');
    }

    /**
     * Test that the restaurant's pages (its reviews page too) have the summary of its approved
     * reviews, right away.
     *
     * @return void
     */
    public function testPagesHaveTheSummary()
    {
        $url = route('web.restaurant.preview', ['locale' => 'en', 'restaurant_id' => $this->restaurant->id]);

        $this->get($url)->assertOk()->assertViewHas('reviews', fn (array $summary) => $summary['count'] === 0);

        RestaurantReview::factory()->withRestaurant($this->restaurant)->approved()->create(['rating' => 4]);

        $this->get($url)->assertViewHas('reviews', fn (array $summary) => $summary['count'] === 1
            && $summary['average'] === 4.0);

        // the reviews page
        $this->get(route('web.reviews.preview', ['locale' => 'en', 'restaurant_id' => $this->restaurant->id]))
            ->assertOk()
            ->assertViewIs('web.app')
            ->assertViewHas('reviews', fn (array $summary) => $summary['count'] === 1);

        $this->get(route('web.reviews.preview', ['locale' => 'en', 'restaurant_id' => 999]))->assertNotFound();
    }
}
