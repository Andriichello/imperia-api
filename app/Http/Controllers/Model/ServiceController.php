<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Service\IndexServiceRequest;
use App\Http\Requests\Service\ShowServiceRequest;
use App\Http\Resources\Service\ServiceCollection;
use App\Http\Resources\Service\ServiceResource;
use App\Repositories\ServiceRepository;

/**
 * Class ServiceController.
 */
class ServiceController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = ServiceResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = ServiceCollection::class;

    /**
     * ServiceController constructor.
     *
     * @param ServiceRepository $repository
     */
    public function __construct(ServiceRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexServiceRequest::class;
        $this->actions['show'] = ShowServiceRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/services",
     *   summary="Index services.",
     *   operationId="indexServices",
     *   security={{"bearerAuth": {}}},
     *   tags={"services"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/ServiceIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="filter[title]", required=false, in="query", example="Clown",
     *     @OA\Schema(type="string"), description="Can be used for searches. Is partial."),
     *   @OA\Parameter(name="filter[categories]", required=false, in="query", example="2,3",
     *     @OA\Schema(type="string"), description="Coma-separated array of category ids. Limits services to those
     * that have at least one of given categories attached to them"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index services response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexServiceResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/services/{id}",
     *   summary="Show service by id.",
     *   operationId="showService",
     *   security={{"bearerAuth": {}}},
     *   tags={"services"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the services."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/ServiceIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show services response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowServiceResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexServiceResponse",
     *   description="Index services response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Service")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowServiceResponse",
     *   description="Show services response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Service"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ServiceIncludes",
     *   description="Coma-separated list of inluded relations.
    Available relations: `categories`",
     *   type="string", example="categories"
     * )
     */
}
