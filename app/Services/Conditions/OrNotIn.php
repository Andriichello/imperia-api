<?php

namespace App\Services\Conditions;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;

class OrNotIn extends Condition
{
    public function __construct(string $name, mixed $value)
    {
        parent::__construct($name, 'or not in', Arr::wrap($value));
    }

    public function query(EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder
    {
        return $query->orWhereNotIn($this->name, $this->value);
    }
}
