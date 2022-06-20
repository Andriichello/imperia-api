<?php

namespace App\Policies;

use App\Models\FamilyMember;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Class FamilyMemberPolicy.
 */
class FamilyMemberPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return FamilyMember::class;
    }

    /**
     * Perform pre-authorization checks.
     *
     * @param User $user
     * @param string $ability
     *
     * @return Response|bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before(User $user, string $ability): Response|bool|null
    {
        return $user->isStaff();
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
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param FamilyMember $member
     *
     * @return bool
     */
    public function view(User $user, FamilyMember $member): bool
    {
        return $member->relative_id === $user->customer_id || $user->isStaff();
    }
}
