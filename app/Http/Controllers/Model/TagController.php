<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Tag\IndexTagRequest;
use App\Http\Requests\Tag\ShowTagRequest;
use App\Http\Resources\Tag\TagCollection;
use App\Http\Resources\Tag\TagResource;
use App\Policies\TagPolicy;
use App\Repositories\TagRepository;
use OpenApi\Annotations as OA;

/**
 * Class TagController.
 */
class TagController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = TagResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = TagCollection::class;

    /**
     * TagController constructor.
     *
     * @param TagRepository $repository
     * @param TagPolicy $policy
     */
    public function __construct(TagRepository $repository, TagPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexTagRequest::class;
        $this->actions['show'] = ShowTagRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/tags",
     *   summary="Index tags.",
     *   operationId="indexTags",
     *   security={{"bearerAuth": {}}},
     *   tags={"tags"},
     *
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="filter[restaurants]", required=false, in="query", example="1",
     *   @OA\Schema(type="string"), description="Coma-separated array of restaurant ids. Limits tags to those
         that are attached at least to one of those restaurants"),
     *   @OA\Parameter(name="filter[target]", in="query", example="products", @OA\Schema(type="string"),
     *     description="Target class morph slug. Examples: `products`"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index tags response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexTagResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/tags/{id}",
     *   summary="Show tag by id.",
     *   operationId="showTag",
     *   security={{"bearerAuth": {}}},
     *   tags={"tags"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the tag."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show tag response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowTagResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexTagResponse",
     *   description="Index tags response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Tag")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowTagResponse",
     *   description="Show tag response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Tag"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
