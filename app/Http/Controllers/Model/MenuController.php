<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\CrudRequest;
use App\Http\Requests\Menu\IndexMenuRequest;
use App\Http\Requests\Menu\ShowMenuRequest;
use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Menu\MenuResource;
use App\Policies\MenuPolicy;
use App\Queries\MenuQueryBuilder;
use App\Repositories\MenuRepository;
use OpenApi\Annotations as OA;

/**
 * Class MenuController.
 */
class MenuController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = MenuResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = MenuCollection::class;

    /**
     * MenuController constructor.
     *
     * @param MenuRepository $repository
     * @param MenuPolicy $policy
     */
    public function __construct(MenuRepository $repository, MenuPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexMenuRequest::class;
        $this->actions['show'] = ShowMenuRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/menus",
     *   summary="Index menus.",
     *   operationId="indexMenus",
     *   security={{"bearerAuth": {}}},
     *   tags={"menus"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/MenuIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="sort", in="query", example="-popularity", @OA\Schema(type="string"),
            description="Available sorts: `popularity` (is default, but in descending order)"),
     *   @OA\Parameter(name="filter[restaurants]", required=false, in="query", example="1",
     *   @OA\Schema(type="string"), description="Coma-separated array of restaurant ids. Limits menus to those
     * that are attached at least to one of those restaurants"),
     *   @OA\Parameter(name="archived", in="query", @OA\Schema(ref ="#/components/schemas/ArchivedParameter")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index menus response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexMenuResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/menus/{id}",
     *   summary="Show menus by id.",
     *   operationId="showMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"menus"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the menus."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/MenuIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show menus response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowMenuResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexMenuResponse",
     *   description="Index menus response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Menu")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowMenuResponse",
     *   description="Show menu response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Menu"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="MenuIncludes",
     *   description="Coma-separated list of inluded relations.
    Available relations: `products`",
     *   type="string", example="products"
     * )
     */
}
