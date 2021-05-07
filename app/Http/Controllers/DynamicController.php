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
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
    protected $model;

    /**
     * Model's primary key names.
     *
     * @var array
     */
    protected $primaryKeys = ['id'];

    /**
     * Get filtered and paginated collection of models.
     *
     * @return array
     */
    public function index()
    {
        try {
            $collection = $this->all();

            if ($collection->count() > 0) {
                $collection = new ResourceCollection($collection);
                return array_merge(
                    ['success' => true],
                    $collection->toArray(\request()),
                );
            }
        } catch (ValidationException $exception) {
            return [
                'success' => false,
                'errors' => $exception->errors(),
            ];
        } catch (\Exception $exception) {
            dd($exception);

            return [
                'success' => false,
                'errors' => [
                    $exception->getMessage()
                ],
            ];
        }

        return [
            'success' => false,
            'errors' => [
                'Not found.'
            ],
        ];
    }

    /**
     * Get one specific model by it's primary keys.
     *
     * @return array
     * @var mixed|array|null $id
     */
    public function show($id = null)
    {
        try {
            $instance = $this->find($id);

            if (isset($instance)) {
                return [
                    'success' => true,
                    'data' => new Resource($instance),
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => [
                        'Not found.'
                    ],
                ];
            }
        } catch (\Exception $exception) {
            return [
                'success' => false,
                'errors' => [
                    $exception->getMessage(),
                ],
            ];
        }
    }

    /**
     * Insert new model's record into the database.
     *
     * @return array
     */
    public function create()
    {
        $validator = Validator::make(\request()->all(), [
            'data' => Constrainter::getRules(true)
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $validator = Validator::make(
            \request()->get('data'),
            $this->model::getValidationRules(true),
        );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        try {
            $instance = $this->model::create($validator->validated());
            return [
                'success' => true,
                'data' => new Resource($instance),
            ];
        } catch (\Exception $exception) {
            return [
                'success' => false,
                'errors' => [
                    $exception->getMessage()
                ],
            ];
        }
    }

    /**
     * Update model's record in the database.
     *
     * @return array
     */
    public function update($id = null)
    {
        $validator = Validator::make(\request()->all(), [
            'data' => Constrainter::getRules(true)
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        $validator = Validator::make(
            \request()->get('data'),
            $this->model::getValidationRules(false),
        );

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
            ];
        }

        try {
            $instance = $this->find($id, 'data');

            if (isset($instance)) {
                $instance->fill($validator->validated());
                $success = $instance->update();
                if ($success) {
                    return [
                        'success' => true,
                        'data' => new Resource($instance)
                    ];
                } else {
                    return [
                        'success' => false,
                        'errors' => [
                            'Error while updating record in the database',
                        ]
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'errors' => [
                        'Not found.'
                    ],
                ];
            }
        } catch (\Exception $exception) {
            return [
                'success' => false,
                'errors' => [
                    $exception->getMessage(),
                ],
            ];
        }
    }

    /**
     * Delete model's record from the database.
     *
     * @return array
     */
    public function delete($id = null)
    {
        try {
            $instance = $this->find($id);

            if (isset($instance)) {
                $success = $instance->delete();
                return [
                    'success' => $success,
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => [
                        'Not found.'
                    ],
                ];
            }
        } catch (\Exception $exception) {
            return [
                'success' => false,
                'errors' => [
                    $exception->getMessage(),
                ],
            ];
        }
    }

    /**
     * Find instance of model by it's primary keys.
     *
     * @param mixed|array|null $id
     * @param string|null $dataKey
     * @return null
     */
    public function find($id = null, $dataKey = null)
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
        $instance = $this->model::select()
            ->where($conditions)
            ->first();

        return $instance;
    }

    /**
     * Get filtered and sorted collection of the model instances.
     *
     * @param array|null $filters where conditions [[key, comparison, value]]
     * @param array|null $sorts orderBy conditions [key, order]
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function all($filters = null, $sorts = null)
    {
        if (empty($filters)) {
            $filters = $this->whereConditions(\request()->all(), true, true);
        }

        if (empty($sorts)) {
            $sorts = $this->orderByConditions(\request()->all(), true);
        }

        $builder = $this->model::select()
            ->where($filters);
        foreach ($sorts as $key => $order) {
            $builder->orderBy($key, $order);
        }

        return $builder->get();
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
     * @return array
     * @var string
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
     * Get array of specified key => value pairs.
     *
     * @return array
     * @var array $keys
     * @var array $data
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
     * @return mixed
     * @var string $key
     * @var mixed $default
     * @var array|object $data
     */
    public function obtain($data, $key, $default = null)
    {
        if (!isset($data) || !isset($key)) {
            return null;
        }

        $keys = preg_split('(\.)', $key);
        $count = count($keys);
        if ($count > 0) {
            if (is_array($data)) {
                if (empty($data[$keys[0]])) {
                    return $default;
                }

                if ($count === 1) {
                    return ($data[$keys[0]] ?? $default);
                } else {
                    $key = implode(
                        '.',
                        array_slice($keys, 1, $count - 1),
                    );

                    return $this->obtain(
                        $data[$keys[0]],
                        $key,
                        $default,
                    );
                }
            }
            if (is_object($data)) {
                if (empty($data->$keys[0])) {
                    return $default;
                }

                if ($count === 1) {
                    return ($data->$keys[0] ?? $default);
                } else {
                    $key = implode(
                        '.',
                        array_slice($keys, 1, $count - 1),
                    );

                    return $this->obtain(
                        $data->$keys[0],
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
     * @return array
     * @var array $data
     * @var bool $basedOnType
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
        return $conditions;
    }

    /**
     * Get array of order by conditions for columns.
     *
     * @return array
     * @var bool $onlyTableColumns
     * @var array $data
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
