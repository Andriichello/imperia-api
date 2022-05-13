<?php

namespace App\Policies;

use App\Models\Space;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TicketPolicy.
 */
class TicketPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Space::class;
    }

    /**
     * Perform pre-authorization checks.
     *
     * @param User $user
     * @param string $ability
     *
     * @return bool
     */
    public function before(User $user, string $ability): bool
    {
        if (in_array($ability, ['viewAny', 'view'])) {
            return true;
        }

        return $user->isAdmin();
    }
}
