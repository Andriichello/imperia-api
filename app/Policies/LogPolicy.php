<?php

namespace App\Policies;

use App\Models\Morphs\Log;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Class LogPolicy.
 */
class LogPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Log::class;
    }

    /**
     * Perform pre-authorization checks.
     *
     * @param User|null $user
     * @param string $ability
     *
     * @return Response|bool|null
     */
    public function before(?User $user, string $ability): Response|bool|null
    {
        if (in_array($ability, ['view', 'viewAny'])) {
            return $user->isStaff();
        }

        return false;
    }
}
