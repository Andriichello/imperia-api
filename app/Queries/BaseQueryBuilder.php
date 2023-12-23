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

    /**
     * Get list of scopes that query has.
     *
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Determines if query has a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->getScopes())
            || array_key_exists($scope, $this->getScopes());
    }

    /**
     * Get list of joins that query has. Keys are aliases
     * and values are table names.
     *
     * @return array
     */
    public function getJoins(): array
    {
        $joins = [];

        // @phpstan-ignore-next-line
        foreach ($this->getQuery()->joins ?? [] as $join) {
            /** @var JoinClause $join */
            $table = $join->table;
            $alias = null;

            $matches = [];
            $pattern = '/(?<table>(\w+|\w+[.]\w+))(\W+as\W+)(?<alias>(\w+|\w+[.]\w+))/';

            if (preg_match($pattern, $table, $matches)) {
                $table = data_get($matches, 'table');
                $alias = data_get($matches, 'alias');
            }

            $joins[$alias ?? $table] = $table;
        }

        return $joins;
    }

    /**
     * Get alias of given joined table. Null is returned if given
     * table is not joined. If there are multiple joins of the
     * given table then only the first alias will be returned.
     *
     *
     * @param string $table
     *
     * @return string|null
     */
    public function getJoinAlias(string $table): ?string
    {
        return array_search($table, $this->getJoins());
    }

    /**
     * Determines if query has a given join by table name and optionally
     * check if it's joined using a given alias.
     *
     * @param string $table
     * @param string|null $alias
     *
     * @return bool
     */
    public function hasJoin(string $table, ?string $alias = null): bool
    {
        $joins = $this->getJoins();

        if (empty($alias)) {
            return in_array($table, $joins);
        }

        return in_array($table, $joins)
            && array_key_exists($alias, $joins);
    }
}
