<?php

namespace App\Imports\Interfaces;

/**
 * Interface ImporterInterface.
 */
interface TransformerInterface
{
    /**
     * Transform record to be imported.
     *
     * @param array $record
     *
     * @return array
     */
    public function transform(array $record): array;

    /**
     * Prepare record before transforming.
     *
     * @param array $record
     *
     * @return array
     */
    public function prepare(array $record): array;
}
