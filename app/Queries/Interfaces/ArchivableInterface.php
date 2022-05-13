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
     * @return BaseQueryBuilder
     */
    public function archived(bool $value): BaseQueryBuilder;
}
