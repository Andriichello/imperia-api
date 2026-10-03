<?php

namespace App\Queries;

use App\Models\MenuVersion;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class MenuVersionQueryBuilder.
 */
class MenuVersionQueryBuilder extends BaseQueryBuilder
{
    /**
     * Versions the user can see: those of their restaurant (all of them for admins
     * of all restaurants), none for guests and customers.
     *
     * @param User|null $user
     *
     * @return static
     */
    public function index(?User $user = null): static
    {
        if (!$user?->isStaff()) {
            return $this->where('menu_versions.id', -1);
        }

        if ($user->restaurant_id) {
            $this->where('menu_versions.restaurant_id', $user->restaurant_id);
        }

        return $this;
    }

    /**
     * Versions, which haven't gone live yet.
     *
     * @return static
     */
    public function pending(): static
    {
        $this->whereIn('menu_versions.status', MenuVersion::PENDING);

        return $this;
    }

    /**
     * Scheduled versions, whose time has come.
     *
     * @return static
     */
    public function due(): static
    {
        $this->where('menu_versions.status', MenuVersion::STATUS_SCHEDULED)
            ->where('menu_versions.goes_live_at', '<=', Carbon::now());

        return $this;
    }

    /**
     * Versions of the restaurant.
     *
     * @param int $restaurantId
     *
     * @return static
     */
    public function ofRestaurant(int $restaurantId): static
    {
        $this->where('menu_versions.restaurant_id', $restaurantId);

        return $this;
    }

    /**
     * Pending versions first, by their date (undated drafts after them), then the
     * ones, which went live, from the latest.
     *
     * @return static
     */
    public function inTimeline(): static
    {
        $statuses = implode(',', array_map(fn ($status) => "'$status'", MenuVersion::PENDING));

        $this->orderByRaw("menu_versions.status in ($statuses) desc")
            ->orderByRaw('menu_versions.goes_live_at is null')
            ->orderByRaw(
                "case when menu_versions.status in ($statuses) then menu_versions.goes_live_at end asc"
            )
            ->orderByDesc('menu_versions.goes_live_at')
            ->orderBy('menu_versions.id');

        return $this;
    }
}
