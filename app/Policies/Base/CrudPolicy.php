<?php

namespace App\Policies\Base;

use App\Http\Requests\CrudRequest;
use App\Http\Requests\Interfaces\WithTargetInterface;
use App\Models\BaseModel;
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
     * @param string $name
     * @param array $arguments
     *
     * @return bool
     */
    public function __call(string $name, array $arguments): bool
    {
        return $this->determineMissing($name, ...$arguments);
    }

    /**
     * Determine if ability should be authorized
     * if method with same name is missing.
     *
     * @param string $ability
     * @param mixed ...$args
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function determineMissing(string $ability, mixed ...$args): bool
    {
        return true;
    }

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

        $before = $this->before($request->user(), $ability);
        if ($before !== null) {
            return $before;
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
     * @param User|null $user
     * @param string $ability
     *
     * @return Response|bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before(?User $user, string $ability): Response|bool|null
    {
        return null;
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
        if ($user->isAdmin()) {
            if (!$target->isAdmin()) {
                return true;
            }

            if ($target->restaurant_id && !$user->restaurant_id) {
                return true;
            }
        }

        return !$target->isStaff() && $user->isStaff();
    }

    /**
     * Determines if user has the rights to interact with
     * target model in context of the user's restaurant.
     *
     * @param User $user
     * @param Model $target
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function restaurantCheck(User $user, Model $target): bool
    {
        if ($user->isAdmin() && !$user->restaurant_id) {
            return true;
        }

        if ($target instanceof BaseModel) {
            return $user->restaurant_id === $target->getRestaurantId();
        }

        $attributes = $target->only('restaurant_id');

        if (empty($attributes)) {
            return true;
        }

        if (!$attributes['restaurant_id']) {
            return false;
        }

        return $user->restaurant_id === $attributes['restaurant_id'];
    }
}
