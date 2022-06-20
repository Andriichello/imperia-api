<?php

namespace App\Queries;

use App\Models\User;

/**
 * Class CustomerQueryBuilder.
 */
class CustomerQueryBuilder extends BaseQueryBuilder
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
        if ($user->isStaff()) {
            return $this;
        }

        $this->where('id', $user->customer_id);

        return $this;
    }
}
