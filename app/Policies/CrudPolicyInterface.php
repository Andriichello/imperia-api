<?php

namespace App\Policies;

use App\Http\Requests\CrudRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Interface CrudPolicyInterface.
 */
interface CrudPolicyInterface
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string;

    /**
     * Determine if user is allowed to perform request.
     *
     * @param CrudRequest $request
     *
     * @return Response|bool
     */
    public function determine(CrudRequest $request): Response|bool;

    /**
     * Perform pre-authorization checks.
     *
     * @param User $user
     * @param string $ability
     *
     * @return bool
     */
    public function before(User $user, string $ability): bool;

    /**
     * Determines if user us higher than the target one.
     *
     * @param User $user
     * @param User $target
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isHigher(User $user, User $target): bool;
}
