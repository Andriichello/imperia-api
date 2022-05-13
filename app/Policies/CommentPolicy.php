<?php

namespace App\Policies;

use App\Models\Banquet;
use App\Models\Customer;
use App\Models\Morphs\Comment;
use App\Models\Orders\Order;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommentPolicy.
 */
class CommentPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Comment::class;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Comment $comment
     *
     * @return bool
     */
    public function update(User $user, Comment $comment): bool
    {

        return $this->hasEditRights($user, $comment);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Comment $comment
     *
     * @return bool
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $this->hasEditRights($user, $comment);
    }

    /**
     * Determine if user has edit rights for the comment.
     *
     * @param User $user
     * @param Comment $comment
     *
     * @return bool
     */
    public function hasEditRights(User $user, Comment $comment): bool
    {
        $target = $comment->commentable;
        if ($target instanceof Order) {
            return $target->canBeEdited();
        }
        if ($target instanceof Banquet) {
            return $target->canBeEdited();
        }

        if ($user->isStaff()) {
            return true;
        }
        // customer shouldn't be able to edit comments on him
        return ($target instanceof Customer) === false;
    }
}
