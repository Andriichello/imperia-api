<?php

namespace App\Queries;

use App\Models\RestaurantReview;
use Carbon\CarbonInterface;

/**
 * Class RestaurantReviewQueryBuilder.
 *
 * @method RestaurantReview|null first($columns = ['*'])
 * @method RestaurantReview|null firstOrFail($columns = ['*'])
 * @method RestaurantReview|null find($columns = ['*'])
 * @method RestaurantReview|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
 */
class RestaurantReviewQueryBuilder extends BaseQueryBuilder
{
    /**
     * Sorts of public reviews: the newest ones first, the highest rated or the lowest rated ones.
     */
    public const SORTS = ['newest', 'highest', 'lowest'];

    /**
     * Reviews of the restaurant.
     *
     * @param int $restaurantId
     *
     * @return static
     */
    public function ofRestaurant(int $restaurantId): static
    {
        $this->where('restaurant_reviews.restaurant_id', $restaurantId);

        return $this;
    }

    /**
     * Reviews with the status.
     *
     * @param string $status
     *
     * @return static
     */
    public function withStatus(string $status): static
    {
        $this->where('restaurant_reviews.status', $status);

        return $this;
    }

    /**
     * Public reviews: the approved ones.
     *
     * @return static
     */
    public function approved(): static
    {
        return $this->withStatus(RestaurantReview::STATUS_APPROVED);
    }

    /**
     * Reviews left from a device (by its token's hash) since the time.
     *
     * @param string $clientHash
     * @param CarbonInterface $since
     *
     * @return static
     */
    public function fromClientSince(string $clientHash, CarbonInterface $since): static
    {
        $this->where('restaurant_reviews.client_hash', $clientHash)
            ->where('restaurant_reviews.created_at', '>=', $since);

        return $this;
    }

    /**
     * In the order of the sort (see `SORTS`), the newest ones first among equal ratings.
     *
     * @param string $sort
     *
     * @return static
     */
    public function sorted(string $sort): static
    {
        match ($sort) {
            'highest' => $this->orderByDesc('restaurant_reviews.rating'),
            'lowest' => $this->orderBy('restaurant_reviews.rating'),
            default => null,
        };

        $this->orderByDesc('restaurant_reviews.created_at')
            ->orderByDesc('restaurant_reviews.id');

        return $this;
    }
}
