<?php

namespace App\Imports\Interfaces;

use App\Imports\Exceptions\SkipRecord;
use Exception;

/**
 * Interface ImporterInterface.
 */
interface ImporterInterface
{
    /**
     * Import records from source to target.
     *
     * @return int Number of imported records
     * @throws Exception
     */
    public function import(): int;

    /**
     * Perform any additional actions before importing.
     *
     * @param array $record Is passed by reference
     *
     * @return void
     * @throws SkipRecord
     */
    public function before(array &$record): void;

    /**
     * Perform any additional actions after importing.
     *
     * @param array $record Is passed by reference
     *
     * @return void
     */
    public function after(array &$record): void;

    /**
     * Exceptions encountered during last import.
     *
     * @return array
     */
    public function exceptions(): array;

    /**
     * Bad records encountered during last import.
     *
     * @return array
     */
    public function badRecords(): array;
}
