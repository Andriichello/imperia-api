<?php

namespace App\Http\Requests\Traits;

use App\Http\Requests\CrudRequest;
use App\Models\Banquet;
use App\Models\User;

/**
 * @mixin CrudRequest
 */
trait GuardsBanquet
{
    /**
     * Determines if user can edit banquet.
     *
     * @param User $user
     * @param Banquet $banquet
     *
     * @return bool
     */
    public function canEdit(User $user, Banquet $banquet): bool
    {
        if (!$banquet->canBeEdited()) {
            $this->message = 'Banquet can\'t be updated,'
                . ' because it\'s in a non-editable state.';

            return false;
        }

        return $this->canAccess($user, $banquet);
    }

    /**
     * Determines if user can access banquet.
     *
     * @param User $user
     * @param Banquet $banquet
     *
     * @return bool
     */
    public function canAccess(User $user, Banquet $banquet): bool
    {
        if ($this->isByCustomer() && $user->id !== $banquet->creator_id) {
            $this->message = 'You are not a creator of this banquet,'
                . ' so you can\'t update it.';

            return false;
        }

        return $this->isByAdmin() || $this->isByManager();
    }
}
