<?php

namespace App\Queries;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Class BaseQueryBuilder.
 */
class BaseQueryBuilder extends EloquentBuilder
{
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
        $this->addWrappedWhereQuery($query, $boolean);

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
        call_user_func($callback, $query = $this->forWrappedWhere());
        $this->addWrappedWhereQuery($query, 'or');

        return $this;
    }


    /**
     * Create a new query instance for wrapped where condition.
     *
     * @return static
     */
    public function forWrappedWhere(): static
    {
        $builder = $this->model::query();
        /** @var static $builder */
        return $builder;
    }

    /**
     * Add another query builder as a nested where to the query builder.
     *
     * @param BaseQueryBuilder $query
     * @param string $boolean
     *
     * @return static
     */
    public function addWrappedWhereQuery(BaseQueryBuilder $query, string $boolean = 'and'): static
    {
        $this->addNestedWhereQuery($query->getQuery(), $boolean);

        return $this;
    }
}
