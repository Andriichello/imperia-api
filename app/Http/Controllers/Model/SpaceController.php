<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Space\IndexSpaceRequest;
use App\Http\Requests\Space\ShowSpaceRequest;
use App\Http\Resources\Space\SpaceCollection;
use App\Http\Resources\Space\SpaceResource;
use App\Repositories\SpaceRepository;

/**
 * Class SpaceController.
 */
class SpaceController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = SpaceResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = SpaceCollection::class;

    /**
     * SpaceController constructor.
     *
     * @param SpaceRepository $repository
     */
    public function __construct(SpaceRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexSpaceRequest::class;
        $this->actions['show'] = ShowSpaceRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/spaces",
     *   summary="Index spaces.",
     *   operationId="indexSpaces",
     *   security={{"bearerAuth": {}}},
     *   tags={"spaces"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/SpaceIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index spaces response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexSpaceResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/spaces/{id}",
     *   summary="Show space by id.",
     *   operationId="showSpace",
     *   security={{"bearerAuth": {}}},
     *   tags={"spaces"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the spaces."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/SpaceIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show spaces response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowSpaceResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexSpaceResponse",
     *   description="Index spaces response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Space")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowSpaceResponse",
     *   description="Show spaces response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Space"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="SpaceIncludes",
     *   description="Coma-separated list of inluded relations.
    Available relations: `categories`",
     *   type="string", example="categories"
     * )
     */
}
