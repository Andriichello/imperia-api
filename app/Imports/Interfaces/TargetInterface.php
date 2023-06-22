<?php

namespace App\Imports\Interfaces;

use Exception;

/**
 * Interface TargetInterface.
 */
interface TargetInterface
{
    /**
     * Open connection to the target.
     *
     * @return static
     * @throws Exception
     */
    public function open(): static;

    /**
     * Close connection to the target.
     *
     * @return static
     */
    public function close(): static;

    /**
     * Insert a new record.
     *
     * @param array $record
     *
     * @return bool
     */
    public function insert(array $record): bool;

    /**
     * Update existing records.
     *
     * @param array $ids
     * @param array $values
     *
     * @return int Number of updated records
     * @throws Exception
     */
    public function update(array $ids, array $values): int;

    /**
     * Delete existing records.
     *
     * @param array $ids
     *
     * @return int Number of deleted records
     * @throws Exception
     */
    public function delete(array $ids): int;
}
