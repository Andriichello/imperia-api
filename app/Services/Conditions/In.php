<?php

namespace App\Services\Conditions;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;

class In extends Condition
{
    public function __construct(string $name, mixed $value)
    {
        parent::__construct($name, 'in', Arr::wrap($value));
    }

    public function query(EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder
    {
        return $query->whereIn($this->name, $this->value);
    }
}
