<?php

namespace App\Repositories\Interfaces;

use App\Models\Customer;
use App\Models\User;

/**
 * Interface CustomerRepositoryInterface
 */
interface CustomerRepositoryInterface extends CrudRepositoryInterface
{
    /**
     * Create customer from existing user.
     *
     * @param User $user
     * @param string|null $phone
     *
     * @return Customer
     */
    public function createFromUser(User $user, ?string $phone = null): Customer;
}
