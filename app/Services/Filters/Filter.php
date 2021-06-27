<?php

namespace App\Services\Filters;

use App\Services\Queryable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;

abstract class Filter implements Queryable
{
    protected array $conditions;

    /**
     * Filter constructor.
     * @param Queryable[]|Queryable $conditions
     */
    public function __construct(array|Queryable $conditions)
    {
        $this->conditions = Arr::wrap($conditions);
    }

    /**
     * @return Queryable[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function query(EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder
    {
        foreach ($this->conditions as $condition) {
            $query = $condition->query($query);
        }
        return $query;
    }
}
