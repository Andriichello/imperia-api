<?php

namespace App\Policies;

use App\Models\Morphs\Category;
use App\Models\Space;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Class TicketPolicy.
 */
class TicketPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Space::class;
    }

    /**
     * Perform pre-authorization checks.
     *
     * @param User $user
     * @param string $ability
     *
     * @return Response|bool|null
     */
    public function before(User $user, string $ability): Response|bool|null
    {
        if (in_array($ability, ['viewAny', 'view'])) {
            return true;
        }

        return $user->isAdmin();
    }

    /**
     * Determine if user can attach categories to model.
     *
     * @param User $user
     * @param Ticket $ticket
     *
     * @return Response|bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function attachAnyCategory(User $user, Ticket $ticket): Response|bool|null
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can detach category from model.
     *
     * @param User $user
     * @param Ticket $ticket
     * @param Category $category
     *
     * @return Response|bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function detachCategory(User $user, Ticket $ticket, Category $category): Response|bool|null
    {
        return $user->isAdmin();
    }
}
