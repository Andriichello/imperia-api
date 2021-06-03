<?php

namespace App\Http\Controllers;

use App\Constrainters\Constrainter;
use App\Http\Requests\DataFieldRequest;
use App\Http\Requests\StoreRequest;
use App\Http\Requests\UpdateRequest;
use App\Http\Resources\Resource;
use App\Http\Resources\ResourceCollection;
use App\Models\BaseDeletableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller as BaseController;

class DynamicController extends BaseController
{
    /**
     * Controller's model class name. Must extend BaseModel.
     *
     * @var string
     */
    protected ?string $model;

    /**
     * Model's primary key names.
     *
     * @var array
     */
    protected array $primaryKeys = ['id'];

    /**
     * Currently applied sorts.
     *
     * @var array
     */
    protected array $currentSorts = [];

    /**
     * Currently applied filters.
     *
     * @var array
     */
    protected array $currentFilters = [];

    /**
     * Is soft deleting performed by default.
     *
     * @var bool
     */
    protected bool $softDelete = true;

    /**
     * Controller's store method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $storeFormRequest = DataFieldRequest::class;

    /**
     * Controller's update method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $updateFormRequest = DataFieldRequest::class;


    /**
     * Get filtered and paginated collection of models.
     *
     * @return Response
     */
    public function index()
    {
        $collection = $this->allModels(\request());
        if ($collection->count() === 0) {
            abort(404, 'Not found');
        }

        return $this->toResponse(\request(), new ResourceCollection($collection));
    }

    /**
     * Get one specific model by it's primary keys.
     *
     * @param mixed|array|null $id
     * @return Response
     */
    public function show($id = null)
    {
        $instance = $this->findModel(\request(), $id);
        if (empty($instance)) {
            abort(404, 'Not found');
        }

        return $this->toResponse(\request(), new Resource($instance));
    }

    /**
     * Insert new model's record into the database.
     *
     * @return Response
     */
    public function store()
    {
        $request = App::make($this->storeFormRequest);
        $instance = $this->createModel($request->validated()[$request->dataFieldName()]);

        return $this->toResponse($request, new Resource($instance), true, 201);
    }

    /**
     * Update model's record in the database.
     *
     * @return Response
     */
    public function update($id = null)
    {
        $request = App::make($this->updateFormRequest);
        $instance = $this->findModel($request, $id, $request->dataFieldName());

        if (!isset($instance)) {
            abort(404, 'Not found');
        }

        $restore = $request->get('restore', false);
        if ($restore && !$this->restoreModel($instance)) {
            abort(520, 'Error while restoring record in the database.');
        }

        if (!$this->updateModel($instance, $request->validated()[$request->dataFieldName()])) {
            abort(520, 'Error while updating record in the database.');
        }

        return $this->toResponse($request, new Resource($instance));
    }

    /**
     * Delete model's record from the database.
     *
     * @return Response
     */
    public function destroy($id = null)
    {
        $request = \request();
        $instance = $this->findModel($id);

        if (!isset($instance)) {
            abort(404, 'Not found');
        }

        if (!$this->destroyModel($instance, $request->get('soft', $this->softDelete))) {
            abort(520, 'Error while deleting record from the database.');
        }

        return $this->toResponse($request, []);
    }

    /**
     * Find instance of model by it's primary keys.
     *
     * @param Request $request
     * @param mixed|array|null $id
     * @param string|null $dataKey
     * @return Model|null
     */
    public function findModel($request, $id = null, $dataKey = null)
    {
        if (!isset($id)) {
            if (isset($dataKey)) {
                $id = $this->extract($request->get($dataKey), $this->primaryKeys());
            } else {
                $id = $this->extract($request->all(), $this->primaryKeys());
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
        $builder = $this->model::select();
        $builder->where($conditions);

        return $builder->first();
    }

    /**
     * Get filtered and sorted collection of the model instances.
     *
     * @param Request $request
     * @param array|null $filters where conditions [[key, comparison, value]]
     * @param array|null $sorts orderBy conditions [key, order]
     * @param string|null $trashed
     * @return \Illuminate\Support\Collection
     */
    public function allModels($request, $filters = null, $sorts = null)
    {
        if (empty($filters)) {
            $filters = $this->whereConditions($request->all(), true, true);
            $this->currentFilters = $filters;
        }

        if (empty($sorts)) {
            $sorts = $this->orderByConditions($request->all(), true);
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
        if (!$instance->update($columns)) {
            return false;
        }

        $instance->refresh();
        return true;
    }

    /**
     * Delete Model instance from the database.
     *
     * @param Model $instance
     * @param bool $softDelete
     * @return bool
     */
    public function destroyModel(Model $instance, bool $softDelete = true): bool
    {
        if ($instance instanceof BaseDeletableModel && !$softDelete) {
            return $instance->forceDelete();
        }

        return $instance->delete();
    }

    /**
     * Restore Model instance in the database.
     *
     * @param Model $instance
     * @param bool $softDelete
     * @return bool
     */
    public function restoreModel(Model $instance): bool
    {
        if ($instance instanceof BaseDeletableModel) {
            return $instance->restore();
        }
        return true;
    }

    /**
     * Get array of values for response.
     *
     * @param Request $request
     * @param bool $success
     * @param ?int $code
     * @param ResourceCollection|Resource|array
     * @return array
     */
    public function toResponseArray(Request $request, bool $success, ?int $code, $data = []): array
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
            $array['data'] = $data->toArray($request);
        } else if ($data instanceof ResourceCollection) {
            $array = array_merge(
                $array,
                $data->toArray($request),
            );
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
            $message = '';
            foreach ($exception->errors() as $bag => $errors) {
                foreach ($errors as $error) {
                    $message .= $error . ' ';
                }
            }

            return [
                'success' => false,
                'message' => $message,
                'errors' => $exception->errors(),
            ];
        }

        return [
            'success' => false,
            'message' => $exception->getMessage(),
            'errors' => [
                $exception->getMessage(),
            ],
        ];
    }

    /**
     * Convert to response.
     *
     * @param Request $request
     * @param array|Resource|ResourceCollection|Exception $data
     * @param bool $success
     * @param int $code
     * @return Response
     */
    public function toResponse(Request $request, $data, bool $success = true, int $code = 200): Response
    {
        if ($data instanceof Exception) {
            if ($data instanceof ValidationException) {
                $code = (int)$data->status;
            } else {
                $code = (int)$data->getCode();
            }

            if (!in_array($code, array_keys(Response::$statusTexts))) {
                $code = 520; // unknown error status code
            }

            return \Illuminate\Support\Facades\Response::make(
                $this->toExceptionArray($data),
                $code,
            );
        }

        return \Illuminate\Support\Facades\Response::make(
            $this->toResponseArray($request, $success, $code, $data),
            $code,
        );
    }

    /**
     * Get array of names for model's primary keys.
     *
     * @param array|string|null $except
     * @return array
     */
    public function primaryKeys($except = null): array
    {
        if (isset($except)) {
            if (is_string($except)) {
                $except = [$except];
            }

            $keys = [];
            foreach ($this->primaryKeys as $key) {
                if (!in_array($key, $except)) {
                    $keys[] = $key;
                }
            }
            return $keys;
        }

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
        return [$dataKey => Constrainter::getRules($forInsert, ['present', 'array'])];
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
            throw (new ValidationException($validator))->status(400);
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
            return $default;
        }

        $keys = preg_split('(\.)', $key);
        $count = count($keys);
        if ($count > 0) {
            $currentKey = $keys[0];
            if (is_array($data)) {
                if (!key_exists($currentKey, $data)) {
                    return $default;
                }

                if ($count === 1) {
                    return ($data[$currentKey]);
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
                if ($count === 1) {
                    return $data->$currentKey ?? $default;
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

            if (in_array('deleted_at', $columns)) {
                $columns[] = 'trashed';
            }
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
     * Determine if specified data matches where conditions.
     *
     * @param array|object $data
     * @param array $whereConditions
     * @param bool $basedOnType
     * @return bool
     */
    public function isMatchingWhereConditions($data, array $whereConditions, bool $basedOnType = false): bool
    {
        if (empty($whereConditions)) {
            return true;
        }

        if (!is_array($whereConditions[0])) {
            $whereConditions = [$whereConditions];
        }

        $isMatching = true;
        foreach ($whereConditions as $whereCondition) {
            if (!$isMatching) {
                return false;
            }

            $dataValue = $this->obtain($data, $whereCondition[0]);
            $whereValue = $whereCondition[2];

            if ($whereCondition[1] === 'in') {
                if (!is_array($whereValue)) {
                    $whereValue = [$whereValue];
                }
                $isMatching = in_array($dataValue, $whereValue);
            } else if ($whereCondition[1] === 'not in') {
                if (!is_array($whereValue)) {
                    $whereValue = [$whereValue];
                }
                $isMatching = !in_array($dataValue, $whereValue);
            } else if ($whereCondition[1] === 'like') {
                $whereValue = strtolower($whereValue);
                $length = strlen($whereValue);
                if ($length > 2) {
                    $whereValue = substr($whereValue, 1, $length - 2);
                }

                $isMatching = str_contains(strtolower($dataValue), strtolower($whereValue));
            } else if ($whereCondition[1] === '=') {
                if ($basedOnType) {
                    $isMatching = $dataValue === $whereValue;
                } else {
                    $isMatching = $dataValue == $whereValue;
                }
            } else if ($whereCondition[1] === '!=') {
                if ($basedOnType) {
                    $isMatching = $dataValue !== $whereValue;
                } else {
                    $isMatching = $dataValue != $whereValue;
                }
            } else if ($whereCondition[1] === '>=') {
                $isMatching = $dataValue >= $whereValue;
            } else if ($whereCondition[1] === '<=') {
                $isMatching = $dataValue <= $whereValue;
            } else if ($whereCondition[1] === '>') {
                $isMatching = $dataValue > $whereValue;
            } else if ($whereCondition[1] === '<') {
                $isMatching = $dataValue > $whereValue;
            }
        }

        return $isMatching;
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
