<?php

namespace App\Queries\Traits;

use App\Queries\BaseQueryBuilder;

/**
 * Trait Archivable.
 *
 * @mixin BaseQueryBuilder
 */
trait Archivable
{
    /**
     * Only models that are archived.
     *
     * @return $this
     */
    public function archived(bool $value): static
    {
        $this->where('archived', $value);

        return $this;
    }
}
