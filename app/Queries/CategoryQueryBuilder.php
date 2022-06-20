<?php

namespace App\Queries;

/**
 * Class CategoryQueryBuilder.
 */
class CategoryQueryBuilder extends BaseQueryBuilder
{
    /**
     * Limit categories to only those that have given target.
     *
     * @param ?string $target
     *
     * @return static
     */
    public function target(?string $target): static
    {
        $this->where('target', $target);

        return $this;
    }
}
