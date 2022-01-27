<?php

namespace App\Http\Controllers;

use App\Http\Requests\Crud\DestroyRequest;
use App\Http\Requests\Crud\IndexRequest;
use App\Http\Requests\Crud\RestoreRequest;
use App\Http\Requests\Crud\ShowRequest;
use App\Http\Requests\Crud\StoreRequest;
use App\Http\Requests\Crud\UpdateRequest;
use App\Http\Requests\CrudRequest;
use App\Http\Responses\ApiResponse;
use App\Repositories\Interfaces\CrudRepositoryInterface;
use BadMethodCallException;
use Exception;
use HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;
use Throwable;

/**
 * Class CrudController.
 */
abstract class CrudController extends Controller
{
    /**
     * Available controller's actions.
     *
     * @var string[]
     */
    protected array $actions = [
        'index' => IndexRequest::class,
        'show' => ShowRequest::class,
        'store' => StoreRequest::class,
        'update' => UpdateRequest::class,
        'destroy' => DestroyRequest::class,
        'restore' => RestoreRequest::class,
    ];

    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $resourceCollectionClass;

    /**
     * Controller's model crud repository.
     *
     * @var CrudRepositoryInterface
     */
    protected CrudRepositoryInterface $repository;

    /**
     * CrudController constructor.
     *
     * @param CrudRepositoryInterface $repository
     */
    public function __construct(CrudRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get available controller actions.
     *
     * @return string[]
     */
    public function getActions(): array
    {
        return array_keys($this->actions);
    }

    /**
     * Get controller action corresponding request,
     *
     * @param string $action
     * @return string|null
     */
    public function getActionRequest(string $action): ?string
    {
        return data_get($this->actions, $action);
    }

    /**
     * Wrap model into the json resource.
     *
     * @param Model $model
     *
     * @return JsonResource
     */
    public function wrapInResource(Model $model): JsonResource
    {
        return new ($this->resourceClass)($model);
    }

    /**
     * Wrap collection into the json resource collection.
     *
     * @param Collection $collection
     *
     * @return ResourceCollection
     */
    public function wrapInResourceCollection(Collection $collection): ResourceCollection
    {
        return new ($this->resourceCollectionClass)($collection);
    }

    /**
     * Convert model to resource json response.
     *
     * @param Model $model
     * @param int $status
     * @param string $message
     *
     * @return ApiResponse
     */
    public function asResourceResponse(Model $model, int $status = 200, string $message = 'Success'): ApiResponse
    {
        $data = ['data' => $this->wrapInResource($model)];
        return ApiResponse::make($data, $status, $message);
    }

    /**
     * Convert query builder to paginated json response.
     *
     * @param SpatieBuilder|EloquentBuilder|Builder $builder
     * @param int $status
     * @param string $message
     *
     * @return ApiResponse
     * @throws Exception
     */
    public function asPaginatedResponse(SpatieBuilder|EloquentBuilder|Builder $builder, int $status = 200, string $message = 'Success'): ApiResponse
    {
        $paginator = $this->paginateResource($builder, $this->resourceCollectionClass);
        return ApiResponse::make($paginator->toArray(), $status, $message);
    }

    /**
     * Get eloquent query builder instance.
     *
     * @return EloquentBuilder
     */
    protected function builder(): EloquentBuilder
    {
        return $this->repository->builder();
    }

    /**
     * Get spatie query builder instance.
     *
     * @param CrudRequest $request
     *
     * @return SpatieBuilder
     */
    protected function spatieBuilder(CrudRequest $request): SpatieBuilder
    {
        return SpatieBuilder::for($this->builder())
            ->allowedFields($request->getAllowedFields())
            ->allowedSorts($request->getAllowedSorts())
            ->allowedFilters($request->getAllowedFilters())
            ->allowedAppends($request->getAllowedAppends())
            ->allowedIncludes($request->getAllowedIncludes());
    }

    /**
     * Authorize and validate given request.
     *
     * @param CrudRequest $request
     *
     * @return void
     * @throws ValidationException
     * @throws AuthorizationException
     */
    protected function authorizeAndValidate(CrudRequest $request): void
    {
        $this->validate($request, $request->rules());
        try {
            $isAuthorized = $request->authorize();
            $isAuthorized ? $request->passesAuthorization() : $request->failedAuthorization();
        } catch (BadMethodCallException) {
            if (!isset($isAuthorized) || $isAuthorized) {
                return;
            }
            throw new AuthorizationException('User is not authorized to perform this request.');
        }
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     *
     * @throws BadMethodCallException|ValidationException
     */
    public function __call($method, $parameters): mixed
    {
        if (in_array($method, $this->getActions())) {
            return $this->handle($method, request());
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Handle controller action.
     *
     * @param string $action
     * @param Request $request
     *
     * @return ApiResponse
     * @throws ValidationException
     * @throws AuthorizationException
     */
    private function handle(string $action, Request $request): ApiResponse
    {
        /** @var CrudRequest $actionRequest */
        $actionRequest = $this->getActionRequest($action);
        $specificRequest = $actionRequest::createFrom($request);

        $this->authorizeAndValidate($specificRequest);

        $method = 'handle' . ucfirst($action);
        return $this->$method($specificRequest);
    }

    /**
     * Handle index action.
     *
     * @param IndexRequest $request
     *
     * @return ApiResponse
     * @throws Exception
     */
    protected function handleIndex(IndexRequest $request): ApiResponse
    {
        $builder = $this->spatieBuilder($request);
        return $this->asPaginatedResponse($builder);
    }

    /**
     * Handle show action.
     *
     * @param ShowRequest $request
     *
     * @return ApiResponse
     */
    protected function handleShow(ShowRequest $request): ApiResponse
    {
        $model = $this->spatieBuilder($request)
            ->findOrFail($request->id());
        return $this->asResourceResponse($model);
    }

    /**
     * Handle store action.
     *
     * @param StoreRequest $request
     *
     * @return ApiResponse
     */
    protected function handleStore(StoreRequest $request): ApiResponse
    {
        $model = $this->repository->create($request->validated());
        return $this->asResourceResponse($model, 201, 'Created');
    }

    /**
     * Handle update action.
     *
     * @param UpdateRequest $request
     *
     * @return ApiResponse
     * @throws Throwable
     */
    protected function handleUpdate(UpdateRequest $request): ApiResponse
    {
        $model = $this->spatieBuilder($request)
            ->findOrFail($request->id());

        $model->updateOrFail($request->validated());
        return $this->asResourceResponse($model->fresh());
    }

    /**
     * Handle destroy action.
     *
     * @param DestroyRequest $request
     *
     * @return ApiResponse
     * @throws Throwable
     */
    protected function handleDestroy(DestroyRequest $request): ApiResponse
    {
        $model = $this->spatieBuilder($request)
            ->findOrFail($request->id());

        $deleted = $request->force() ? $model->forceDelete() : $model->deleteOrFail();
        if (!$deleted) {
            throw new HttpException('Failed to delete ' . $model::class, 500);
        }

        return ApiResponse::make([], 200, 'Deleted');
    }

    /**
     * Handle restore action.
     *
     * @param RestoreRequest $request
     *
     * @return ApiResponse
     * @throws Throwable
     */
    protected function handleRestore(RestoreRequest $request): ApiResponse
    {
        $model = $this->builder()->withTrashed()
            ->findOrFail($request->id());

        if (!method_exists($model, 'restore')) {
            throw new HttpException("There is no restore method in the " . get_class($model), 400);
        }
        if (!$model->restore()) {
            throw new HttpException("Failed restoring " . get_class($model), 500);
        }
        return $this->asResourceResponse($model->fresh());
    }
}
