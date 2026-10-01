<?php

namespace App\Queries;

use App\Models\Morphs\Comment;
use App\Models\User;
use App\Providers\MorphServiceProvider;

/**
 * Class CommentQueryBuilder.
 *
 * @method Comment|null first($columns = ['*'])
 * @method Comment|null firstOrFail($columns = ['*'])
 * @method Comment|null find($columns = ['*'])
 * @method Comment|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
 */
class CommentQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User|null $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(?User $user = null): static
    {
        if ($user->isStaff()) {
            return $this;
        }

        return $this->asForCustomer($user);
    }

    /**
     * Limit comments to only those that given customer can see.
     *
     * @param User $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asForCustomer(User $user): static
    {
        $this->notForClasses(User::class);

        return $this;
    }

    /**
     * Exclude comments for given commentable classes.
     *
     * @param string ...$classes
     *
     * @return static
     */
    public function notForClasses(string ...$classes): static
    {
        $morphs = MorphServiceProvider::getMorphMap($classes);

        $this->whereNotIn('commentable_type', array_keys($morphs));

        return $this;
    }

    /**
     * Include only comments for given commentable classes.
     *
     * @param string ...$classes
     *
     * @return static
     */
    public function forClasses(string ...$classes): static
    {
        $morphs = MorphServiceProvider::getMorphMap($classes);

        $this->whereIn('commentable_type', array_keys($morphs));

        return $this;
    }

    /**
     * Include only comments with given commentable ids.
     *
     * @param array $values
     *
     * @return static
     */
    public function whereCommentableId(array $values): static
    {
        $this->whereIn('commentable_id', $values);

        return $this;
    }
}
