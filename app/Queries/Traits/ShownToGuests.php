<?php

namespace App\Queries\Traits;

use App\Queries\BaseQueryBuilder;

/**
 * Trait ShownToGuests.
 *
 * @mixin BaseQueryBuilder
 */
trait ShownToGuests
{
    /**
     * Only models guests see: not archived, hidden or deleted, whatever the request asks for
     * (e.g. through relations, which don't apply the archived and soft-deleting scopes).
     *
     * @return static
     */
    public function shownToGuests(): static
    {
        $this->where($this->qualifyColumn('archived'), false)
            ->where($this->qualifyColumn('is_hidden'), false)
            ->whereNull($this->qualifyColumn('deleted_at'));

        return $this;
    }
}
