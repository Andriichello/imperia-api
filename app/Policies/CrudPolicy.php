<?php

namespace App\Policies;

use App\Http\Requests\CrudRequest;
use App\Http\Requests\Interfaces\WithTargetInterface;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Class CrudPolicy.
 */
abstract class CrudPolicy implements CrudPolicyInterface
{
    use HandlesAuthorization;

    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    abstract public function model(): Model|string;

    /**
     * Determine if user is allowed to perform request.
     *
     * @param CrudRequest $request
     *
     * @return Response|bool
     */
    public function determine(CrudRequest $request): Response|bool
    {
        $ability = $request->getAbility();

        if (!$ability) {
            return true;
        }

        if (!$this->before($request->user(), $ability)) {
            return false;
        }

        if (!method_exists($this, $ability)) {
            return true;
        }

        return $request instanceof WithTargetInterface
            ? $this->$ability($request->user(), $request->target($this->model()))
            : $this->$ability($request->user());
    }

    /**
     * Perform pre-authorization checks.
     *
     * @param User $user
     * @param string $ability
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before(User $user, string $ability): bool
    {
        return true;
    }

    /**
     * Determines if user us higher than the target one.
     *
     * @param User $user
     * @param User $target
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isHigher(User $user, User $target): bool
    {
        return !$target->isAdmin() && $user->isAdmin();
    }
}
