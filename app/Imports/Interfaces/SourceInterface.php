<?php

namespace App\Imports\Interfaces;

use Exception;

/**
 * Interface SourceInterface.
 */
interface SourceInterface
{
    /**
     * Open connection to the source.
     *
     * @return static
     * @throws Exception
     */
    public function open(): static;

    /**
     * Close connection to the source.
     *
     * @return static
     */
    public function close(): static;

    /**
     * Returns current record number.
     *
     * @return int|null
     */
    public function position(): ?int;

    /**
     * Returns true if last record was read.
     *
     * @return bool|null
     */
    public function ended(): ?bool;

    /**
     * Returns last read record.
     *
     * @return array|false
     */
    public function last(): array|false;

    /**
     * Read and return next record.
     *
     * @return array|false
     */
    public function next(): array|false;
}
