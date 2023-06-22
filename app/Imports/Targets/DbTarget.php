<?php

namespace App\Imports\Targets;

use App\Imports\Interfaces\TargetInterface;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class DbTarget.
 */
class DbTarget implements TargetInterface
{
    /**
     * @var string
     */
    protected string $connection;

    /**
     * @var string
     */
    protected string $table;

    /**
     * DbTarget constructor.
     *
     * @param string $connection
     * @param string $table
     */
    public function __construct(string $table, string $connection = 'mysql')
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * Returns new instance of query builder for the target database table.
     *
     * @return Builder
     */
    public function builder(): Builder
    {
        return DB::connection($this->connection)
            ->table($this->table);
    }

    /**
     * Add nested statement to filter out record by ids.
     *
     * @param Builder $builder
     * @param array $ids
     *
     * @return Builder
     * @throws Exception
     */
    public function whereIds(Builder $builder, array $ids): Builder
    {
        if (empty($ids)) {
            throw new Exception('The ids array cannot be empty.');
        }

        $closure = function (Builder $query) use ($ids) {
            foreach ($ids as $column => $value) {
                $query->where($column, '=', $value);
            }
        };

        return $builder->where($closure);
    }

    /**
     * Inserts a new record.
     *
     * @param array $record
     *
     * @return bool
     */
    public function insert(array $record): bool
    {
        return $this->builder()->insert($record);
    }

    /**
     * Updates existing records.
     *
     * @param array $ids
     * @param array $values
     *
     * @return int Number of updated records
     * @throws Exception
     */
    public function update(array $ids, array $values): int
    {
        return $this->whereIds($this->builder(), $ids)
            ->update($values);
    }

    /**
     * Delete existing records.
     *
     * @param array $ids
     *
     * @return int Number of deleted records
     * @throws Exception
     */
    public function delete(array $ids): int
    {
        return $this->whereIds($this->builder(), $ids)
            ->delete();
    }

    /**
     * Open connection to the target.
     *
     * @return static
     * @throws Exception
     */
    public function open(): static
    {
        return $this;
    }

    /**
     * Close connection to the target.
     *
     * @return static
     */
    public function close(): static
    {
        return $this;
    }
}
