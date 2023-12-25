<?php

namespace App\Queries;

use App\Models\User;

/**
 * Class FamilyMemberQueryBuilder.
 */
class FamilyMemberQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User|null $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(?User $user = null): static
    {
        if ($user->isStaff()) {
            return $this;
        }

        $this->where('relative_id', $user->customer_id);

        return $this;
    }
}
