<?php

namespace App\Queries\Interfaces;

use App\Models\User;
use App\Queries\BaseQueryBuilder;

/**
 * Interface IndexableInterface.
 */
interface IndexableInterface
{
    /**
     * Apply index query conditions.
     *
     * @param User $user
     *
     * @return BaseQueryBuilder
     */
    public function index(User $user): BaseQueryBuilder;
}
