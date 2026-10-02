<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Class SoftDeletableScope.
 *
 * @method Builder withTrashed()
 * @method Builder withoutTrashed()
 * @method Builder onlyTrashed()
 */
class SoftDeletableScope extends SoftDeletingScope
{
    /**
     * Whether the current user is being looked up.
     *
     * @var bool
     */
    protected static bool $resolvingUser = false;

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model): void
    {
        $type = 'without';

        // Only staff can choose to see deleted records, guests always get the default
        if ($this->isStaff($this->currentUser())) {
            $type = request('filter.deleted', request('deleted', 'without'));
        }

        if (in_array($type, ['only', 'with', 'without'])) {
            $method = $type . 'Trashed';
            $builder->$method();
        }
    }

    /**
     * The current user. Users are soft-deletable too, so while the user itself is
     * being looked up, its query gets the default (deleted users can't sign in).
     *
     * @return User|null
     */
    protected function currentUser(): ?User
    {
        if (static::$resolvingUser) {
            return null;
        }

        static::$resolvingUser = true;

        try {
            return request()->user();
        } finally {
            static::$resolvingUser = false;
        }
    }

    /**
     * Whether the user is staff (an admin or a manager).
     *
     * @param mixed $user
     *
     * @return bool
     */
    protected function isStaff(mixed $user): bool
    {
        return $user instanceof User && $user->isStaff();
    }
}
