<?php

namespace App\Services;

use App\Services\Conditions\Equal;
use App\Services\Conditions\On;
use App\Services\Conditions\OrEqual;
use App\Services\Conditions\OrOn;
use App\Services\Filters\Join;
use App\Services\Filters\OrWhere;
use App\Services\Filters\Where;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Query
{
    protected array $queryables;
    protected EloquentBuilder|QueryBuilder|null $query;

    public function __construct(array $queryables = [], EloquentBuilder|QueryBuilder $query = null)
    {
        $this->query = $query;
        $this->queryables = $queryables;
    }

    public static function from(EloquentBuilder|QueryBuilder $query, array $queryables = []): static
    {
        return static::create($queryables, $query);
    }

    public static function create(array $queryables = [], EloquentBuilder|QueryBuilder $query = null): static
    {
        return new static($queryables, $query);
    }

    public function queryables(): array
    {
        return $this->queryables;
    }

    public function apply(EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder
    {
        foreach ($this->queryables as $queryable) {
            $query = $queryable->query($query);
        }

        return $query;
    }

    public function finish(): EloquentBuilder|QueryBuilder
    {
        return $this->apply($this->query);
    }

    public function append(Queryable $queryable): static
    {
        $this->queryables[] = $queryable;
        return $this;
    }

    public function equal(string $name, mixed $value): static
    {
        return $this->append(new Equal($name, $value));
    }

    public function orEqual(string $name, mixed $value): static
    {
        return $this->append(new OrEqual($name, $value));
    }

    public function where(\Closure $closure): static
    {
        $query = static::create();
        $closure->call($query, $query);

        return $this->append(new Where($query->queryables));
    }

    public function orWhere(\Closure $closure): static
    {
        $query = static::create();
        $closure->call($query, $query);

        return $this->append(new OrWhere($query->queryables));
    }

    public function on(string $name, mixed $value): static
    {
        return $this->append(new On($name, $value));
    }

    public function orOn(string $name, mixed $value): static
    {
        return $this->append(new OrOn($name, $value));
    }

    public function join(string $table, \Closure $closure): static
    {
        $query = static::create();
        $closure->call($query, $query);

        return $this->append(new Join($table, $query->queryables));
    }

}
