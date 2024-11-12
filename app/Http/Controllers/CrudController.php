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
use App\Models\BaseModel;
use App\Policies\Base\CrudPolicyInterface;
use App\Repositories\Interfaces\CrudRepositoryInterface;
use BadMethodCallException;
use Exception;
use HttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;
use Throwable;

/**
 * Class CrudController.
 *
 * @method ApiResponse index(IndexRequest $request)
 * @method ApiResponse show(ShowRequest $request)
 * @method ApiResponse store(StoreRequest $request)
 * @method ApiResponse update(UpdateRequest $request)
 * @method ApiResponse destroy(DestroyRequest $request)
 * @method ApiResponse restore(RestoreRequest $request)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * Controller's model crud repository.
     *
     * @var CrudPolicyInterface
     */
    protected CrudPolicyInterface $policy;

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
     * @param CrudPolicyInterface $policy
     */
    public function __construct(CrudRepositoryInterface $repository, CrudPolicyInterface $policy)
    {
        $this->repository = $repository;
        $this->policy = $policy;
    }

    /**
     * Get eloquent query builder instance.
     *
     * @param CrudRequest $request
     *
     * @return EloquentBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function builder(CrudRequest $request): EloquentBuilder
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
        $builder = $request->spatieBuilder($this->builder($request));

        if ($this->repository->isSoftDeletable()) {
            /** @phpstan-ignore-next-line */
            $builder->withTrashed();
        }

        return $builder;
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
     * Handle calls to missing methods on the controller.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     *
     * @throws BadMethodCallException|ValidationException|AuthorizationException
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

        $this->checkPolicy($specificRequest);
        $specificRequest->validateResolved();

        $method = 'handle' . ucfirst($action);
        return $this->$method($specificRequest);
    }

    /**
     * Check if user is authorized to perform request.
     *
     * @param CrudRequest $request
     *
     * @return void
     * @throws AuthorizationException
     */
    protected function checkPolicy(CrudRequest $request): void
    {
        $result = empty($this->policy)
            || $this->policy->determine($request);

        if ($result === false) {
            throw new AuthorizationException();
        }
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
        /** @var BaseModel $model */
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

        /** @var BaseModel $model */
        $model = $this->spatieBuilder($request)
            ->findOrFail($model->getKey());

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
        /** @var BaseModel $model */
        $model = $this->spatieBuilder($request)
            ->findOrFail($request->id());

        $this->repository->update($model, $request->validated());
        return $this->asResourceResponse($model->refresh());
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
        /** @var BaseModel $model */
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
        /** @var BaseModel $model */
        $model = $this->spatieBuilder($request)
            ->findOrFail($request->id());

        if (!method_exists($model, 'restore')) {
            throw new HttpException("There is no restore method in the " . get_class($model), 400);
        }

        if (!$model->restore()) {
            throw new HttpException("Failed restoring " . get_class($model), 500);
        }

        return $this->asResourceResponse($model->refresh());
    }
}
