<?php

namespace App\Repositories;

use App\Models\Restaurant;
use App\Models\RestaurantReview;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Class RestaurantReviewRepository.
 *
 * Reviews guests leave on the public site, and their moderation. Only approved reviews are public:
 * they're the only ones in the list, the average, the count and the counts by rating.
 */
class RestaurantReviewRepository
{
    /**
     * Public reviews of a page.
     */
    public const PER_PAGE = 10;

    /**
     * Reviews of a page of moderation.
     */
    public const MODERATION_PER_PAGE = 20;

    /**
     * Hours, within which a device leaves one review of a restaurant.
     */
    public const DEVICE_LIMIT_HOURS = 24;

    /**
     * The approved reviews of the restaurant: their average (to one decimal, none without reviews),
     * count and counts by rating from 5 down to 1.
     *
     * @param Restaurant $restaurant
     *
     * @return array{average: float|null, count: int, ratings: array<int, array{rating: int, count: int}>}
     */
    public function summary(Restaurant $restaurant): array
    {
        $counts = RestaurantReview::query()
            ->ofRestaurant($restaurant->id)
            ->approved()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $ratings = array_map(
            fn (int $rating) => ['rating' => $rating, 'count' => (int) ($counts[$rating] ?? 0)],
            [5, 4, 3, 2, 1],
        );
        $count = array_sum(array_column($ratings, 'count'));
        $sum = array_sum(array_map(fn (array $item) => $item['rating'] * $item['count'], $ratings));

        return [
            'average' => $count ? round($sum / $count, 1) : null,
            'count' => $count,
            'ratings' => $ratings,
        ];
    }

    /**
     * A page of the approved reviews of the restaurant, in the order of the sort.
     *
     * @param Restaurant $restaurant
     * @param string $sort `newest`, `highest` or `lowest`
     * @param int $page
     *
     * @return LengthAwarePaginator
     */
    public function approved(Restaurant $restaurant, string $sort, int $page): LengthAwarePaginator
    {
        return RestaurantReview::query()
            ->ofRestaurant($restaurant->id)
            ->approved()
            ->sorted($sort)
            ->paginate(static::PER_PAGE, ['*'], 'page', $page);
    }

    /**
     * Leave a review of the restaurant: it waits for the restaurant to approve it.
     *
     * @param Restaurant $restaurant
     * @param array $data `rating`, `name`, `text`, `locale`
     * @param string $ipAddress of the guest
     * @param string $clientToken of the guest's device
     *
     * @return RestaurantReview
     */
    public function create(
        Restaurant $restaurant,
        array $data,
        string $ipAddress,
        string $clientToken,
    ): RestaurantReview {
        /** @var RestaurantReview $review */
        $review = RestaurantReview::query()->create([
            'restaurant_id' => $restaurant->id,
            'rating' => $data['rating'],
            'name' => $data['name'] ?? null,
            'text' => $data['text'] ?? null,
            'locale' => $data['locale'],
            'ip_hash' => RestaurantReview::hash($ipAddress),
            'client_hash' => RestaurantReview::hash($clientToken),
        ]);

        return $review->refresh();
    }

    /**
     * Whether the device has left a review of the restaurant lately (see `DEVICE_LIMIT_HOURS`).
     *
     * @param Restaurant $restaurant
     * @param string $clientToken
     *
     * @return bool
     */
    public function postedLately(Restaurant $restaurant, string $clientToken): bool
    {
        return RestaurantReview::query()
            ->ofRestaurant($restaurant->id)
            ->fromClientSince(RestaurantReview::hash($clientToken), Carbon::now()->subHours(static::DEVICE_LIMIT_HOURS))
            ->exists();
    }

    /**
     * The device's own reviews of the restaurant among the ids (whatever their status): nobody
     * else sees a review, which waits for approval.
     *
     * @param Restaurant $restaurant
     * @param int[] $ids
     * @param string $clientToken
     *
     * @return Collection<int, RestaurantReview>
     */
    public function mine(Restaurant $restaurant, array $ids, string $clientToken): Collection
    {
        /** @var Collection<int, RestaurantReview> $reviews */
        $reviews = RestaurantReview::query()
            ->ofRestaurant($restaurant->id)
            ->whereIn('restaurant_reviews.id', $ids)
            ->where('restaurant_reviews.client_hash', RestaurantReview::hash($clientToken))
            ->orderBy('restaurant_reviews.id')
            ->get();

        return $reviews;
    }

    /**
     * A page of the restaurant's reviews with the status, the newest ones first (moderation).
     *
     * @param Restaurant $restaurant
     * @param string $status
     * @param int $page
     *
     * @return LengthAwarePaginator
     */
    public function withStatus(Restaurant $restaurant, string $status, int $page): LengthAwarePaginator
    {
        return RestaurantReview::query()
            ->ofRestaurant($restaurant->id)
            ->withStatus($status)
            ->with('moderator')
            ->sorted('newest')
            ->paginate(static::MODERATION_PER_PAGE, ['*'], 'page', $page);
    }

    /**
     * How many reviews of the restaurant have each status.
     *
     * @param Restaurant $restaurant
     *
     * @return array<string, int>
     */
    public function counts(Restaurant $restaurant): array
    {
        $counts = RestaurantReview::query()
            ->ofRestaurant($restaurant->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return array_combine(
            RestaurantReview::STATUSES,
            array_map(fn (string $status) => (int) ($counts[$status] ?? 0), RestaurantReview::STATUSES),
        );
    }

    /**
     * Approve or reject the review (it may be decided again, e.g. personal details found later).
     *
     * @param RestaurantReview $review
     * @param string $status `approved` or `rejected`
     * @param User|null $user who decided
     *
     * @return RestaurantReview
     */
    public function moderate(RestaurantReview $review, string $status, ?User $user): RestaurantReview
    {
        $review->update([
            'status' => $status,
            'moderated_at' => Carbon::now(),
            'moderated_by' => $user?->id,
        ]);

        return $review->load('moderator');
    }
}
