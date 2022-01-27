<?php

namespace App\Http\Requests\Traits;

use App\Http\Requests\BaseRequest;

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
}
