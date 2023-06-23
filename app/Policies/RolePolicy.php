<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;

/**
 * Class RolePolicy.
 */
class RolePolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Role::class;
    }

    /**
     * Perform pre-authorization checks.
     *
     * @param User|null $user
     * @param string $ability
     *
     * @return Response|bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before(?User $user, string $ability): Response|bool|null
    {
        if (in_array($ability, ['view', 'viewAny'])) {
            return !$user->isPreviewOnly() && $user->isAdmin();
        }

        return false;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
