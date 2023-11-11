<?php

namespace App\Queries\Interfaces;

/**
 * Interface ArchivableInterface.
 */
interface ArchivableInterface
{
    /**
     * Only models that are archived.
     *
     * @param bool $value
     *
     * @return static
     */
    public function archived(bool $value): static;
}
