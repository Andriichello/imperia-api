<?php

namespace App\Imports\Targets;

use App\Imports\Interfaces\TargetInterface;
use Exception;

/**
 * Class MockTarget.
 */
class MockTarget implements TargetInterface
{
    /**
     * Inserts a new record.
     *
     * @param array $record
     *
     * @return bool
     */
    public function insert(array $record): bool
    {
        return true;
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
        return 1;
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
        return 1;
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
