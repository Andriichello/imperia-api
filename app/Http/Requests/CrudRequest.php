<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Http\Requests\Traits\WithSpatie;
use App\Models\User;

/**
 * Class CrudRequest.
 */
class CrudRequest extends BaseRequest
{
    use WithSpatie;

    /**
     * Get the user making the request.
     *
     * @param mixed $guard
     *
     * @return User
     */
    public function user(mixed $guard = null): User
    {
        return parent::user($guard);
    }

    /**
     * Determine if user, who makes the request is an admin.
     *
     * @return bool
     */
    public function isByAdmin(): bool
    {
        return $this->user()->hasRole(UserRole::Admin);
    }

    /**
     * Determine if user, who makes the request is a manager.
     *
     * @return bool
     */
    public function isByManager(): bool
    {
        return $this->user()->hasRole(UserRole::Manager);
    }
}
