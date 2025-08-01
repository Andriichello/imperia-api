<?php

namespace App\Policies\Base;

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
     * Determine if ability should be authorized
     * if method with same name is missing.
     *
     * @param string $ability
     * @param mixed ...$args
     *
     * @return bool
     */
    public function determineMissing(string $ability, mixed ...$args): bool;

    /**
     * Perform pre-authorization checks.
     *
     * @param User|null $user
     * @param string $ability
     *
     * @return Response|bool|null
     */
    public function before(?User $user, string $ability): Response|bool|null;

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
