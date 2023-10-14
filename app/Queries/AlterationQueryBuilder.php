<?php

namespace App\Queries;

use App\Models\User;
use App\Providers\MorphServiceProvider;

/**
 * Class AlterationQueryBuilder.
 */
class AlterationQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(User $user): static
    {
        if ($user->isStaff()) {
            return $this;
        }

        return $this->where('id', -1);
    }

    /**
     * Exclude alterations for given alterable classes.
     *
     * @param string ...$classes
     *
     * @return static
     */
    public function notForClasses(string ...$classes): static
    {
        $morphs = MorphServiceProvider::getMorphMap($classes);

        $this->whereNotIn('alterable_type', array_keys($morphs));

        return $this;
    }

    /**
     * Include only alterations for given alterable classes.
     *
     * @param string ...$classes
     *
     * @return static
     */
    public function forClasses(string ...$classes): static
    {
        $morphs = MorphServiceProvider::getMorphMap($classes);

        $this->whereIn('alterable_type', array_keys($morphs));

        return $this;
    }

    /**
     * Include only alterations with given alterable ids.
     *
     * @param array $values
     *
     * @return static
     */
    public function whereAlterableId(array $values): static
    {
        $this->whereIn('alterable_id', $values);

        return $this;
    }
}
