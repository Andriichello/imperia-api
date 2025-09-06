<?php

namespace App\Queries;

use App\Models\FamilyMember;
use App\Models\User;

/**
 * Class FamilyMemberQueryBuilder.
 *
 * @method FamilyMember|null first($columns = ['*'])
 * @method FamilyMember|null firstOrFail($columns = ['*'])
 * @method FamilyMember|null find($columns = ['*'])
 * @method FamilyMember|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
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
