<?php

namespace App\Queries;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class UserQueryBuilder.
 */
class UserQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(User $user): static
    {
        if ($user->restaurant_id) {
            $this->withRestaurant($user->restaurant_id);
        }

        if ($user->isAdmin()) {
            return $this;
        }

        if ($user->isManager()) {
            $this->whereWrapped(function (UserQueryBuilder $query) use ($user) {
                $query->onlyRoles(UserRole::Customer)
                    ->orWhere('id', $user->id);
            });

            return $this;
        }

        return $this->where('id', $user->id);
    }

    /**
     * Only users that have given roles.
     *
     * @param string ...$roles
     *
     * @return $this
     */
    public function onlyRoles(string ...$roles): static
    {
        if (empty($roles) === false) {
            $this->whereHas('roles', function (Builder $builder) use ($roles) {
                $builder->whereIn('roles.name', $roles);
            });
        }

        return $this;
    }

    /**
     * Except users that have given roles.
     *
     * @param string ...$roles
     *
     * @return $this
     */
    public function exceptRoles(string ...$roles): static
    {
        if (empty($roles) === false) {
            $this->whereDoesntHave('roles', function (Builder $builder) use ($roles) {
                $builder->whereIn('roles.name', $roles);
            });
        }

        return $this;
    }

    /**
     * Only users related to given restaurants.
     *
     * @param mixed ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(mixed ...$restaurants): static
    {
        $ids = $this->extract('id', ...$restaurants);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }
}
