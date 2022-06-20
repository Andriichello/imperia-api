<?php

namespace App\Policies;

use App\Models\Banquet;
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
        if ($target instanceof Order && !$target->canBeEdited()) {
            return false;
        }
        if ($target instanceof Banquet && !$target->canBeEdited()) {
            return false;
        }

        if ($user->isStaff()) {
            return true;
        }

        return Comment::query()
            ->asForCustomer($user)
            ->whereKey($comment->id)
            ->exists();
    }
}
