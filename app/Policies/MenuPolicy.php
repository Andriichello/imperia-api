<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MenuPolicy.
 */
class MenuPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Menu::class;
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
        if (in_array($ability, ['view', 'viewAny'])) {
            return true;
        }

        return $user->isStaff();
    }
}
