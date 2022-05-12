<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class UserQueryBuilder.
 */
class UserQueryBuilder extends BaseQueryBuilder
{
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
}
