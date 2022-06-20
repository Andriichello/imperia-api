<?php

namespace App\Queries\Interfaces;

use App\Queries\BaseQueryBuilder;

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
