<?php

namespace App\Http\Controllers\Traits;

use App\Http\Resources\ResourcePaginator;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

use function config;
use function request;

/**
 * Trait PaginatesTrait.
 */
trait PaginationTrait
{
    /**
     * Paginate query builder.
     *
     * @param Builder|EloquentBuilder|SpatieBuilder $builder
     *
     * @return Paginator|LengthAwarePaginator
     */
    public function paginate(SpatieBuilder|EloquentBuilder|Builder $builder): Paginator|LengthAwarePaginator
    {
        $maxResults = config('pagination.max_results');
        $defaultSize = config('pagination.default_size');
        $numberParameter = config('pagination.number_parameter');
        $sizeParameter = config('pagination.size_parameter');
        $paginationParameter = config('pagination.pagination_parameter');
        $paginationMethod = config('pagination.use_simple_pagination') ? 'simplePaginate' : 'paginate';

        $size = (int)request()->input($paginationParameter . '.' . $sizeParameter, $defaultSize);

        $size = min($size, $maxResults);

        $paginator = $builder
            ->{$paginationMethod}($size, ['*'], $paginationParameter . '.' . $numberParameter)
            ->setPageName($paginationParameter . '[' . $numberParameter . ']')
            ->appends(Arr::except(request()->input(), $paginationParameter . '.' . $numberParameter));

        if (!is_null(config('pagination.base_url'))) {
            $paginator->setPath(config('pagination.base_url'));
        }

        return $paginator;
    }

    /**
     * Paginate query builder to a resource paginator.
     *
     * @param Builder|EloquentBuilder|SpatieBuilder $builder
     * @param string|null $collectionClass
     *
     * @return ResourcePaginator
     * @throws Exception
     */
    public function paginateResource(
        Builder|EloquentBuilder|SpatieBuilder $builder,
        ?string $collectionClass,
    ): ResourcePaginator {
        return new ResourcePaginator($this->paginate($builder), $collectionClass);
    }
}
