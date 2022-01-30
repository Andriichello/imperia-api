<?php

namespace App\Http\Requests\Traits;

use App\Http\Requests\BaseRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Trait WithSpatie.
 *
 * @mixin BaseRequest
 */
trait WithSpatie
{
    /**
     * Allowed sorts for spatie query builder.
     *
     * @var array
     */
    protected array $allowedSorts = [];

    /**
     * Allowed fields for spatie query builder.
     *
     * @var array
     */
    protected array $allowedFields = [];

    /**
     * Allowed appends for spatie query builder.
     *
     * @var array
     */
    protected array $allowedAppends = [];

    /**
     * Allowed filters for spatie query builder.
     *
     * @var array
     */
    protected array $allowedFilters = [];

    /**
     * Allowed includes for spatie query builder.
     *
     * @var array
     */
    protected array $allowedIncludes = [];

    /**
     * Get sorts fields for spatie query builder.
     *
     * @return array
     */
    public function getAllowedSorts(): array
    {
        return $this->allowedSorts;
    }

    /**
     * Get allowed fields for spatie query builder.
     *
     * @return array
     */
    public function getAllowedFields(): array
    {
        return $this->allowedFields;
    }

    /**
     * Get allowed appends for spatie query builder.
     *
     * @return array
     */
    public function getAllowedAppends(): array
    {
        return $this->allowedAppends;
    }

    /**
     * Get allowed filters for spatie query builder.
     *
     * @return array
     */
    public function getAllowedFilters(): array
    {
        return $this->allowedAppends;
    }

    /**
     * Get allowed includes for spatie query builder.
     *
     * @return array
     */
    public function getAllowedIncludes(): array
    {
        return $this->allowedIncludes;
    }

    /**
     * Apply allowed options to spatie builder.
     *
     * @param Builder|EloquentBuilder|SpatieBuilder $builder
     *
     * @return SpatieBuilder
     */
    public function spatieBuilder(Builder|EloquentBuilder|SpatieBuilder $builder): SpatieBuilder
    {
        $builder = $builder instanceof SpatieBuilder ? $builder : SpatieBuilder::for($builder);

        return $builder->allowedFields($this->getAllowedFields())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedFilters($this->getAllowedFilters())
            ->allowedAppends($this->getAllowedAppends())
            ->allowedIncludes($this->getAllowedIncludes());
    }
}
