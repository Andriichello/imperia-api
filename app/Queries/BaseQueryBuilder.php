<?php

namespace App\Queries;

use App\Models\User;
use App\Queries\Interfaces\IndexableInterface;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;

/**
 * Class BaseQueryBuilder.
 */
class BaseQueryBuilder extends EloquentBuilder implements IndexableInterface
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
        return $this;
    }

    /**
     * Create a new query instance for wrapped where condition.
     *
     * @return static
     */
    public function forWrappedWhere(): static
    {
        /** @var static $builder */
        $builder = $this->model->newModelQuery();

        return $builder;
    }

    /**
     * Add a wrapped where statement to the query.
     *
     * @param Closure $callback
     * @param string $boolean
     *
     * @return static
     */
    public function whereWrapped(Closure $callback, string $boolean = 'and'): static
    {
        call_user_func($callback, $query = $this->forWrappedWhere());
        $this->addNestedWhereQuery($query->getQuery(), $boolean);

        return $this;
    }

    /**
     * Add a wrapped orWhere statement to the query.
     *
     * @param Closure $callback
     *
     * @return static
     */
    public function orWhereWrapped(Closure $callback): static
    {
        return $this->whereWrapped($callback, 'or');
    }

    /**
     * Extract column from given items.
     *
     * @param string $key
     * @param mixed ...$items
     *
     * @return array
     */
    protected function extract(string $key, mixed ...$items): array
    {
        return extractValues($key, ...$items);
    }

    /**
     * Only records within given dates.
     *
     * @param Carbon|null $beg
     * @param Carbon|null $end
     * @param string $column
     *
     * @return $this
     */
    public function within(?Carbon $beg, ?Carbon $end, string $column = 'created_at'): static
    {
        if (isset($beg) && empty($end)) {
            $this->where($column, '>=', $beg);
        }

        if (empty($beg) && isset($end)) {
            $this->where($column, '<=', $end);
        }

        if (isset($beg) && isset($end)) {
            $this->whereNested(function (Builder $query) use ($beg, $end, $column) {
                $query->where($column, '>=', $beg)
                    ->where($column, '<=', $end);
            });
        }

        return $this;
    }
}
