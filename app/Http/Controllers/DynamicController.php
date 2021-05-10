<?php

namespace App\Http\Controllers;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\EmailConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Constrainters\Implementations\PhoneConstrainter;
use App\Custom\Collection;
use App\Http\Resources\Resource;
use App\Http\Resources\ResourceCollection;
use App\Models\BaseModel;
use App\Models\Customer;
use Facade\FlareClient\Http\Exceptions\NotFound;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller as BaseController;
use Symfony\Component\Console\Output\ConsoleOutput;

class DynamicController extends BaseController
{
    /**
     * Controller's model class name. Must extend BaseModel.
     *
     * @var string
     */
    protected $model;

    /**
     * Model's primary key names.
     *
     * @var array
     */
    protected $primaryKeys = ['id'];

    /**
     * Currently applied sorts.
     *
     * @var array
     */
    protected $currentSorts = [];

    /**
     * Currently applied filters.
     *
     * @var array
     */
    protected $currentFilters = [];

    /**
     * Get filtered and paginated collection of models.
     *
     * @return array
     */
    public function index()
    {
        try {
            $collection = $this->allModels();

            if ($collection->count() > 0) {
                return $this->toResponseArray(true, 200, new ResourceCollection($collection));
            } else {
                throw new Exception('Not found', 404);
            }
        } catch (\Exception $exception) {
            return $this->toExceptionArray($exception);
        }
    }

    /**
     * Get one specific model by it's primary keys.
     *
     * @param mixed|array|null $id
     * @return array
     */
    public function show($id = null)
    {
        try {
            $instance = $this->findModel($id);

            if (isset($instance)) {
                return $this->toResponseArray(true, 200, new Resource($instance));
            } else {
                throw new Exception('Not found', 404);
            }
        } catch (\Exception $exception) {
            return $this->toExceptionArray($exception);
        }
    }

    /**
     * Insert new model's record into the database.
     *
     * @return array
     */
    public function store()
    {
        try {
            $data = $this->validateRules(\request()->all(), $this->getDataValidationRules('data'));
            $data = $this->validateRules($data['data'], $this->getModelValidationRules(true));

            $instance = $this->createModel($data);

            return $this->toResponseArray(true, 200, new Resource($instance));
        } catch (\Exception $exception) {
            return $this->toExceptionArray($exception);
        }
    }

    /**
     * Update model's record in the database.
     *
     * @return array
     */
    public function update($id = null)
    {
        try {
            $data = $this->validateRules(\request()->all(), $this->getDataValidationRules('data'));
            $data = $this->validateRules($data['data'], $this->getModelValidationRules(false));

            $instance = $this->findModel($id, 'data');

            if (isset($instance)) {
                if ($this->updateModel($instance, $data)) {
                    return $this->toResponseArray(true, 200, new Resource($instance));
                } else {
                    throw new Exception('Error while updating record in the database.');
                }
            } else {
                throw new Exception('Not found');
            }
        } catch (\Exception $exception) {
            return $this->toExceptionArray($exception);
        }
    }

    /**
     * Delete model's record from the database.
     *
     * @return array
     */
    public function destroy($id = null)
    {
        try {
            $instance = $this->findModel($id);

            if (isset($instance)) {
                if ($this->destroyModel($instance)) {
                    return $this->toResponseArray(true, 200);
                } else {
                    throw new Exception('Error while deleting record from the database.');
                }
            } else {
                throw new Exception('Not found');
            }
        } catch (\Exception $exception) {
            return $this->toExceptionArray($exception);
        }
    }

    /**
     * Find instance of model by it's primary keys.
     *
     * @param mixed|array|null $id
     * @param string|null $dataKey
     * @return Model|null
     */
    public function findModel($id = null, $dataKey = null)
    {
        if (!isset($id)) {
            if (isset($dataKey)) {
                $id = $this->extract(\request()->get($dataKey), $this->primaryKeys());
            } else {
                $id = $this->extract(\request()->all(), $this->primaryKeys());
            }
        }

        if (!is_array($id)) {
            if (count($this->primaryKeys()) === 1) {
                $id = [$this->primaryKeys()[0] => $id];
            } else {
                return null;
            }
        }

        if (count($id) !== count($this->primaryKeys())) {
            return null;
        }

        $conditions = $this->extract($id, $this->primaryKeys());
        return $this->model::select()
            ->where($conditions)
            ->first();
    }

    /**
     * Get filtered and sorted collection of the model instances.
     *
     * @param array|null $filters where conditions [[key, comparison, value]]
     * @param array|null $sorts orderBy conditions [key, order]
     * @return \Illuminate\Support\Collection
     */
    public function allModels($filters = null, $sorts = null)
    {
        if (empty($filters)) {
            $filters = $this->whereConditions(\request()->all(), true, true);
            $this->currentFilters = $filters;
        }

        if (empty($sorts)) {
            $sorts = $this->orderByConditions(\request()->all(), true);
            $this->currentSorts = $sorts;
        }

        $builder = $this->model::select();
        foreach ($filters as $filter) {
            if (
                is_array($filter) &&
                count($filter) === 3
            ) {
                if ($filter[1] === 'in') {
                    $builder->whereIn($filter[0], $filter[2]);
                } else if ($filter[1] === 'not in') {
                    $builder->whereNotIn($filter[0], $filter[2]);
                } else {
                    $builder->where($filters);
                }
                continue;
            }
            $builder->where($filters);
        }

        foreach ($sorts as $key => $order) {
            $builder->orderBy($key, $order);
        }

        return $builder->get();
    }

    /**
     * Create new Model instance and store it in the database.
     *
     * @param array $columns
     * @return Model
     */
    public function createModel(array $columns): Model
    {
        return $this->model::create($columns);
    }

    /**
     * Update Model instance in the database.
     *
     * @param Model $instance
     * @param array $columns
     * @return bool
     */
    public function updateModel(Model $instance, array $columns = []): bool
    {
        return $instance->update($columns);
    }

    /**
     * Delete Model instance from the database.
     *
     * @param Model $instance
     * @return bool
     */
    public function destroyModel(Model $instance): bool
    {
        return $instance->delete();
    }


    /**
     * Get array of values for response.
     *
     * @param bool $success
     * @param ?int $code
     * @param ResourceCollection|Resource|array
     * @return array
     */
    public function toResponseArray(bool $success, ?int $code, $data = []): array
    {
        $array = ['success' => $success];

        // displaying applied sorting parameters, should be removed on release
        $array['sorts'] = [];
        foreach ($this->currentSorts as $columnName => $sortOrder) {
            $array['sorts'][] = [$columnName, $sortOrder];
        }

        // displaying applied filtering parameters, should be removed on release
        $array['filters'] = $this->currentFilters;

        if ($data instanceof Resource) {
            $array['data'] = $data->toArray(\request());
        } else if ($data instanceof ResourceCollection) {
            $array['data'] = $data->toArray(\request());
        } else if (is_array($data)) {
            $array = array_merge(
                $array,
                $data,
            );
        }

        return $array;
    }

    /**
     * Get array of values for exception response.
     *
     * @param Exception $exception
     * @return array
     */
    public function toExceptionArray(Exception $exception): array
    {
        if ($exception instanceof ValidationException) {
            return [
                'success' => false,
                'errors' => $exception->errors(),
            ];
        }

        return [
            'success' => false,
            'errors' => [
                $exception->getMessage(),
            ],
        ];
    }

    /**
     * Get array of names for model's primary keys.
     *
     * @return array
     */
    public function primaryKeys()
    {
        return $this->primaryKeys;
    }

    /**
     * Get table name.
     *
     * @return string
     */
    public function tableName()
    {
        return (new $this->model)->getTable();
    }

    /**
     * Get table column names.
     *
     * @param string
     * @return array
     */
    public function tableColumns($tableName = null)
    {
        return Schema::getColumnListing($tableName ?? $this->tableName());
    }

    /**
     * Get array of names for model's fillable columns.
     *
     * @return array
     */
    public function fillable()
    {
        return (new $this->model)->getFillable();
    }

    /**
     * Get array of model's columns name to type pairs.
     *
     * @return array
     */
    public function types()
    {
        $tableName = $this->tableName();
        $tableColumns = $this->tableColumns($tableName);

        $types = [];
        foreach ($tableColumns as $tableColumn) {
            $types[$tableColumn] = Schema::getColumnType($tableName, $tableColumn);
        }

        return $types;
    }

    /**
     * Get array of model's validation rules.
     *
     * @param bool $forInsert
     * @return array
     */
    public function getModelValidationRules(bool $forInsert = false): array
    {
        if (isset($this->model)) {
            return $this->model::getValidationRules($forInsert);
        }

        return [];
    }

    /**
     * Get array of input data array validation rules.
     *
     * @param string $dataKey
     * @param bool $forInsert
     * @return array
     */
    public function getDataValidationRules(string $dataKey = '', bool $forInsert = false): array
    {
        if (empty($dataKey)) {
            return [];
        }
        return [$dataKey => Constrainter::getRules(true)];
    }

    /**
     * Validate data with the array of rules.
     *
     * @param array $data
     * @param array $rules
     * @return array
     *
     * @throws ValidationException
     */
    public function validateRules(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated();
    }

    /**
     * Get array of specified key => value pairs.
     *
     * @param array $keys
     * @param array $data
     * @return array
     */
    public function extract(array $data, array $keys)
    {
        if (empty($data) || empty($keys)) {
            return [];
        }

        $filtered = [];
        $filteredKeys = array_intersect(array_keys($data), $keys);
        foreach ($filteredKeys as $filteredKey) {
            $filtered[$filteredKey] = $data[$filteredKey];
        }
        return $filtered;
    }

    /**
     * Obtain values from array or object.
     *
     * @param string $key
     * @param mixed $default
     * @param array|object $data
     * @return mixed
     */
    public function obtain($data, $key, $default = null)
    {
        if (!isset($data) || !isset($key)) {
            return null;
        }

        $keys = preg_split('(\.)', $key);
        $count = count($keys);
        if ($count > 0) {
            $currentKey = $keys[0];
            if (is_array($data)) {
                if (empty($data[$currentKey])) {
                    return $default;
                }

                if ($count === 1) {
                    return ($data[$currentKey] ?? $default);
                } else {
                    $key = implode(
                        '.',
                        array_slice($keys, 1, $count - 1),
                    );

                    return $this->obtain(
                        $data[$currentKey],
                        $key,
                        $default,
                    );
                }
            }
            if (is_object($data)) {
                if (empty($data->$currentKey)) {
                    return $default;
                }

                if ($count === 1) {
                    return ($data->$currentKey ?? $default);
                } else {
                    $key = implode(
                        '.',
                        array_slice($keys, 1, $count - 1),
                    );

                    return $this->obtain(
                        $data->$currentKey,
                        $key,
                        $default,
                    );
                }
            }
        }
        return $data;
    }

    /**
     * Get array of where conditions for columns.
     *
     * @param array $data
     * @param bool $basedOnType
     * @return array
     */
    public function whereConditions(array $data, bool $basedOnType = false, bool $onlyTableColumns = true)
    {
        if (empty($data)) {
            return [];
        }

        if ($basedOnType) {
            $types = $this->types();
        }

        if ($onlyTableColumns) {
            $columns = $this->tableColumns();
        }

        $conditions = [];
        foreach ($data as $key => $value) {
            if (
                isset($columns) &&
                !in_array($key, $columns)
            ) {
                continue;
            }

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
                if (
                    isset($types) &&
                    isset($types[$key]) &&
                    $types[$key] === 'string'
                ) {
                    if (in_array($key, $this->primaryKeys())) {
                        $conditions[] = [$key, 'like', $value];
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
     * Get array of order by conditions for columns.
     *
     * @param bool $onlyTableColumns
     * @param array $data
     * @return array
     */
    public function orderByConditions(array $data, bool $onlyTableColumns = false)
    {
        if (empty($data) || empty($data['sort'])) {
            return [];
        }

        $sort = [];
        if (is_string($data['sort'])) {
            $columns = preg_split('(\s*,\s*)', $data['sort']);
            foreach ($columns as $column) {
                $length = strlen($column);
                if ($length > 0) {
                    if (preg_match('(-\S{1,})', $column)) {
                        $sort[substr($column, 1, $length - 1)] = 'desc';
                    } else {
                        $sort[$column] = 'asc';
                    }
                }
            }

            if ($onlyTableColumns) {
                $columns = $this->tableColumns();
                foreach ($sort as $key => $order) {
                    if (!in_array($key, $columns)) {
                        unset($sort[$key]);
                    }
                }
            }
        }

        return $sort;
    }
}
