<?php

namespace App\Queries;

use App\Models\Morphs\Alteration;
use App\Models\User;
use App\Providers\MorphServiceProvider;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AlterationQueryBuilder.
 *
 * @method Alteration|null first($columns = ['*'])
 * @method Alteration|null firstOrFail($columns = ['*'])
 * @method Alteration|null find($columns = ['*'])
 * @method Alteration|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
 */
class AlterationQueryBuilder extends BaseQueryBuilder
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
        if (!$user?->isStaff()) {
            return $this->where('id', -1);
        }

        if ($user->restaurant_id) {
            $this->where('alterations.restaurant_id', $user->restaurant_id);
        }

        return $this;
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

    /**
     * Include only alterations, which have been performed
     * based on the `performed_at` column value.
     *
     * @return static
     */
    public function thatHaveBeenPerformed(): static
    {
        $this->whereNotNull('performed_at');

        return $this;
    }

    /**
     * Include only alterations, which have not been performed
     * based on the `performed_at` column value.
     *
     * @return static
     */
    public function thatHaveNotBeenPerformed(): static
    {
        $this->whereNull('performed_at');

        return $this;
    }

    /**
     * Include only alterations with the given status (see `Alteration::STATUS_*`).
     *
     * @param string $status
     *
     * @return static
     */
    public function withStatus(string $status): static
    {
        match ($status) {
            Alteration::STATUS_DONE => $this->whereNotNull('performed_at'),
            Alteration::STATUS_FAILED => $this->whereNull('performed_at')
                ->whereNotNull('failed_at'),
            Alteration::STATUS_SCHEDULED => $this->whereNull('performed_at')
                ->whereNull('failed_at')
                ->where('perform_at', '>', now()),
            Alteration::STATUS_DUE => $this->thatShouldBePerformed(),
            default => null,
        };

        return $this;
    }

    /**
     * Include only alterations, which should be performed
     * based on the `perform_at` column value. Failed ones are
     * skipped, so they aren't retried on every run.
     *
     * @return static
     */
    public function thatShouldBePerformed(): static
    {
        $shouldBePerformed = function (Builder $query) {
            $query->whereNull('perform_at')
                ->orWhere('perform_at', '<=', now());
        };

        $this->thatHaveNotBeenPerformed()
            ->whereNull('failed_at')
            ->where($shouldBePerformed);

        return $this;
    }
}
