<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\PaginationTrait;
use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class Controller.
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
    use PaginationTrait;
    use DispatchesJobs;

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
    protected string $collectionClass;

    /**
     * Wrap model into the json resource.
     *
     * @param Model $model
     *
     * @return JsonResource
     */
    public function wrapInResource(Model $model): JsonResource
    {
        $class = $this->resourceClass;
        return new $class($model);
    }

    /**
     * Wrap collection into the json resource collection.
     *
     * @param Collection $collection
     *
     * @return ResourceCollection
     */
    public function wrapInCollection(Collection $collection): ResourceCollection
    {
        $class = $this->collectionClass;
        return new $class($collection);
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
    public function asPaginatedResponse(
        SpatieBuilder|EloquentBuilder|Builder $builder,
        int $status = 200,
        string $message = 'Success',
    ): ApiResponse {
        $paginator = $this->paginateResource($builder, $this->collectionClass);
        return ApiResponse::make($paginator->toArray(), $status, $message);
    }
}
