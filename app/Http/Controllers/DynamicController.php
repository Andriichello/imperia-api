<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Controllers\Traits\DynamicallyFilterable;
use App\Http\Controllers\Traits\DynamicallyIdentifiable;
use App\Http\Controllers\Traits\DynamicallySortable;
use App\Http\Requests\DynamicFormRequest;
use App\Http\Resources\PaginatedResourceCollection;
use App\Models\BaseDeletableModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DynamicController extends BaseController
{
    use DynamicallyIdentifiable, DynamicallyFilterable, DynamicallySortable;

    /**
     * Dynamic form request request.
     *
     * @var DynamicFormRequest
     */
    protected DynamicFormRequest $request;

    /**
     * Get dynamic form request
     *
     * @return DynamicFormRequest
     */
    public function request(): DynamicFormRequest
    {
        return $this->request;
    }

    public function __construct(DynamicFormRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Get filtered and paginated collection of models.
     *
     * @return Response
     */
    public function index(): Response
    {
        $collection = $this->allModels($this->request()->all());
        if ($collection->count() === 0) {
            abort(404, 'Not found');
        }

        return $this->toResponse($this->request(), new PaginatedResourceCollection($collection));
    }

    /**
     * Get one specific model by it's primary keys.
     *
     * @param mixed $id
     * @return Response
     */
    public function show(mixed $id = null): Response
    {
        $instance = $this->findModel($this->request(), $id);
        if (!isset($instance)) {
            abort(404, 'Not found');
        }

        return $this->toResponse($this->request(), new JsonResource($instance));
    }

    /**
     * Insert new model's record into the database.
     *
     * @return Response
     */
    public function store(): Response
    {
        $instance = $this->createModel($this->request()->validated()[$this->request()->dataFieldName()]);

        return $this->toResponse($this->request(), new JsonResource($instance), true, 201);
    }

    /**
     * Update model's record in the database.
     *
     * @param mixed $id
     * @return Response
     */
    public function update(mixed $id = null): Response
    {
        $instance = $this->findModel($this->request(), $id, $this->request()->dataFieldName());

        if (!isset($instance)) {
            abort(404, 'Not found');
        }

        $restore = $this->request()->get('restore', false);
        if ($restore && !$this->restoreModel($instance)) {
            abort(520, 'Error while restoring record in the database.');
        }

        if (!$this->updateModel($instance, $this->request()->validated()[$this->request()->dataFieldName()])) {
            abort(520, 'Error while updating record in the database.');
        }

        return $this->toResponse($this->request(), new JsonResource($instance));
    }

    /**
     * Delete model's record from the database.
     *
     * @param mixed $id
     * @return Response
     */
    public function destroy(mixed $id = null): Response
    {
        $instance = $this->findModel($id);

        if (!isset($instance)) {
            abort(404, 'Not found');
        }

        if (!$this->destroyModel($instance, $this->request()->get('soft', $this->softDelete))) {
            abort(520, 'Error while deleting record from the database.');
        }

        return $this->toResponse($this->request(), []);
    }

    /**
     * Find instance of model by it's primary keys.
     *
     * @param DynamicFormRequest $request
     * @param mixed|null $id
     * @param string|null $dataKey
     * @return Model|null
     */
    public function findModel(DynamicFormRequest $request, mixed $id = null, ?string $dataKey = null): ?Model
    {
        if (!isset($id)) {
            if (!empty($dataKey)) {
                $id = $this->extract($request->get($dataKey), $this->primaryKeys());
            } else {
                $id = $this->extract($request->all(), $this->primaryKeys());
            }
        }

        if (!is_array($id)) {
            // there are more primary keys than specified values
            if (count($this->primaryKeys()) > 1) {
                return null;
            }

            // set id to an associative array
            $id = [$this->primaryKeys()[array_key_first($this->primaryKeys())] => $id];
        }

        // specified values count differs from count of needed for identification columns
        if (count($id) !== count($this->primaryKeys())) {
            return null;
        }

        return $this->allModels($request->all(), $id)->first();
    }

    /**
     * Get filtered and sorted collection of the model instances.
     *
     * @param array $data
     * @param array $additionalParameters
     * @return Collection
     */
    public function allModels(array $data, array $additionalParameters = []): Collection
    {
        $data = array_merge($data, $additionalParameters);

        // init query builder
        $queryBuilder = $this->model::select();

        [$modelFilters, $additionalFilters] = $this->extractFilters($data);
        // append model filters to select query
        $queryBuilder = $this->applyModelFilters($queryBuilder, $modelFilters);

        [$modelSorts, $additionalSorts] = $this->extractSorts($data);
        // append model sorts to select query
        $queryBuilder = $this->applyModelSorts($queryBuilder, $modelSorts);

        // retrieve filtered and sorted collection
        $collection = $queryBuilder->get();

        // apply additional filters and sorts on a collection
        $collection = $this->applyAdditionalFilters($collection, $additionalFilters);
        $collection = $this->applyAdditionalSorts($collection, $additionalSorts);

        return $collection;
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
     * @param PaginatedResourceCollection|JsonResource|array
     * @return array
     */
    public function toResponseArray(Request $request, bool $success, $data = []): array
    {
        $array = ['success' => $success];

        // displaying applied sorting parameters, should be removed on release
        $array['sorts'] = [];
        foreach ($this->appliedSorts as $columnName => $sortOrder) {
            $array['sorts'][] = [$columnName, $sortOrder];
        }

        // displaying applied filtering parameters, should be removed on release
        $array['filters'] = $this->appliedFilters;

        if ($data instanceof PaginatedResourceCollection) {
            $array = array_merge(
                $array,
                $data->toArray($request),
            );
        } else if ($data instanceof JsonResource) {
            $array['data']  = $data->toArray($request);
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
            foreach ($exception->errors() as $errors) {
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
     * @param Exception|array|JsonResource|ResourceCollection $data
     * @param bool $success
     * @param int $code
     * @return Response
     */
    public function toResponse(Request $request, array|JsonResource|ResourceCollection|Exception $data, bool $success = true, int $code = 200): Response
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
            $this->toResponseArray($request, $success, $data),
            $code,
        );
    }
}
