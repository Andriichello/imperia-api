<?php

namespace App\Http\Filters;

use App\Queries\Interfaces\CategorizableInterface;
use App\Queries\Interfaces\TaggableInterface;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class TagsFilter.
 *
 * Filters items to those that have at least one of the
 * specified tags attached to them.
 *
 * @package App\Http\Filters
 */
class TagsFilter implements Filter
{
    use BaseFilterTrait;

    /**
     * @param Builder $query
     * @param mixed $value
     * @param string $property
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if ($query instanceof TaggableInterface) {
            $query->withAnyOfTags(...$this->extract($value));
        }
    }
}
