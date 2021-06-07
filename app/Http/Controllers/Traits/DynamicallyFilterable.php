<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait DynamicallyFilterable
{
    use DynamicallyIdentifiable;

    /**
     * Filters that can be applied directly to model's table.
     *
     * @var array|null
     */
    protected ?array $modelFilters = null;

    /**
     * Additional filters that should be applied on a collection.
     *
     * @var array
     */
    protected array $additionalFilters = [];

    /**
     * Applied filters.
     *
     * @var array
     */
    protected array $appliedFilters = [];

    /**
     * Get available filters.
     *
     * @return array
     */
    public function getAvailableFilters(): array
    {
        return array_merge($this->getModelFilters(), $this->getAdditionalFilters());
    }

    /**
     * Get model's filters.
     *
     * @return array
     */
    public function getModelFilters(): array
    {
        if ($this->modelFilters === null) {
            return $this->tableColumns();
        }
        return $this->modelFilters;
    }

    /**
     * Get additional filters that should be applied on a collection.
     *
     * @return array
     */
    public function getAdditionalFilters(): array
    {
        return $this->additionalFilters;
    }

    /**
     * Get currently applied filters.
     *
     * @return array
     */
    public function getAppliedFilters(): array
    {
        return $this->appliedFilters;
    }

    /**
     * Get only those filters that are specified in a second argument.
     *
     * @return array
     */
    protected function limitFilters(array $requestedFilters, array $availableFilters)
    {
        if (empty($availableFilters)) {
            return [];
        }

        $limitedFilters = [];
        foreach ($requestedFilters as $filter) {
            if (!is_array($filter) || !in_array($filter[0], $availableFilters)) {
                continue;
            }
            $limitedFilters[] = $filter;
        }
        return $limitedFilters;
    }

    /**
     * Get model and additional filters.
     *
     * @return array
     */
    protected function extractFilters(array $data)
    {
        if (empty($data)) {
            return [[], []];
        }

        $filters = $this->whereConditions($data, true);
        return [
            $this->limitFilters($filters, $this->getModelFilters()),
            $this->limitFilters($filters, $this->getAdditionalFilters()),
        ];
    }

    protected function applyFilters(Collection|Builder $data, array $filters): Collection|Builder
    {
        $modelFilters = $this->limitFilters($filters, $this->getModelFilters());
        $additionalFilters = $this->limitFilters($filters, $this->getAdditionalFilters());

        $data = $this->applyModelFilters($data, $modelFilters);
        $data = $this->applyAdditionalFilters($data, $additionalFilters);

        return $data;
    }

    protected function applyModelFilters(Collection|Builder $data, array $filters): Collection|Builder
    {
        if (empty($filters)) {
            return $data;
        }

        if ($data instanceof Collection) {
            return $data->filter(function ($item) use ($filters) {
                return $this->isMatchingWhereConditions($item, $filters, false);
            });
        }

        foreach ($filters as $filter) {
            if (is_array($filter)) {
                if (count($filter) === 2) {
                    $data->where($filter[0], $filter[1]);
                } else if (count($filter) === 3) {
                    if ($filter[1] === 'in') {
                        $data->whereIn($filter[0], $filter[2]);
                    } else if ($filter[1] === 'not in') {
                        $data->whereNotIn($filter[0], $filter[2]);
                    } else {
                        $data->where($filter[0], $filter[1], $filter[2]);
                    }
                }
            }
        }

        $this->appliedFilters = array_merge($this->appliedFilters, $filters);
        return $data;
    }

    protected function applyAdditionalFilters(Collection|Builder $data, array $filters): Collection|Builder
    {
        // implement additional filtering logic
        return $data;
    }

    /**
     * Get array of where conditions for columns.
     *
     * @param array $data
     * @param bool $basedOnType
     * @return array
     */
    public function whereConditions(array $data, bool $basedOnType = false): array
    {
        if (empty($data)) {
            return [];
        }

        if ($basedOnType) {
            $types = $this->types();
        }

        $conditions = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value['min'])) {
                    $conditions[] = [$key, '>=', $value['min']];
                }
                if (isset($value['max'])) {
                    $conditions[] = [$key, '<=', $value['max']];
                }
                if (isset($value['only'])) {
                    $ins = preg_split('(,)', $value['only']);
                    $conditions[] = [$key, 'in', $ins];
                }
                if (isset($value['except'])) {
                    $outs = preg_split('(,)', $value['except']);
                    $conditions[] = [$key, 'not in', $outs];
                }
            } else {
                if ($key === 'trashed') {
                    if ($value === 'only') {
                        $conditions[] = ['deleted_at', '!=', null];
                    } else if ($value === 'with') {
//                        $conditions[] = ['deleted_at', '=', null];
                    } else if ($value === 'without') {
                        $conditions[] = ['deleted_at', '=', null];
                    }
                } else if (
                    isset($types) &&
                    isset($types[$key]) &&
                    $types[$key] === 'string'
                ) {
                    if (in_array($key, $this->primaryKeys())) {
                        $conditions[] = [$key, '=', $value];
                    } else {
                        $conditions[] = [$key, 'like', '%' . $value . '%'];
                    }
                } else {
                    $conditions[] = [$key, '=', $value];
                }
            }
        }
        return $conditions;
    }

    /**
     * Determine if specified data matches where condition.
     *
     * @param array|object $data
     * @param array $whereCondition [key, condition, value/[values]]
     * @param bool $strict
     * @return bool
     */
    public function isMatchingWhereCondition(array|object $data, array $whereCondition, bool $strict = false): bool
    {
        if (empty($whereCondition)) {
            return true;
        }

        $dataValue = data_get($data, $whereCondition[0]);
        $whereValue = $whereCondition[2];

        switch ($whereCondition[1]) {
            case 'in':
                return in_array($dataValue, Arr::wrap($whereValue));

            case 'not in':
                return !in_array($dataValue, Arr::wrap($whereValue));

            case 'like':
                $whereValue = strtolower($whereValue);
                $length = strlen($whereValue);
                if ($length > 2) {
                    $whereValue = substr($whereValue, 1, $length - 2);
                }
                return str_contains(strtolower($dataValue), strtolower($whereValue));

            case '=':
                return $strict ? $dataValue === $whereValue : $dataValue == $whereValue;

            case '!=':
                return $strict ? $dataValue !== $whereValue : $dataValue != $whereValue;

            case '>=':
                return $dataValue >= $whereValue;

            case '<=':
                return $dataValue <= $whereValue;

            case '>':
                return $dataValue > $whereValue;

            case '<':
                return $dataValue < $whereValue;
        }

        return true;
    }


    /**
     * Determine if specified data matches where conditions.
     *
     * @param array|object $data
     * @param array $whereConditions [[key, condition, value/[values]], ...]
     * @param bool $strict
     * @return bool
     */
    public function isMatchingWhereConditions(array|object $data, array $whereConditions, bool $strict = false): bool
    {
        $whereConditions = Arr::wrap($whereConditions);
        foreach ($whereConditions as $whereCondition) {
            if (!$this->isMatchingWhereCondition($data, $whereCondition, $strict)) {
                return false;
            }
        }
        return true;
    }
}
