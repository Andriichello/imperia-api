<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\CrudRequest;
use App\Http\Requests\FamilyMember\IndexFamilyMemberRequest;
use App\Http\Requests\FamilyMember\ShowFamilyMemberRequest;
use App\Http\Requests\FamilyMember\StoreFamilyMemberRequest;
use App\Http\Requests\FamilyMember\UpdateFamilyMemberRequest;
use App\Http\Resources\FamilyMember\FamilyMemberCollection;
use App\Http\Resources\FamilyMember\FamilyMemberResource;
use App\Policies\FamilyMemberPolicy;
use App\Queries\FamilyMemberQueryBuilder;
use App\Repositories\FamilyMemberRepository;
use OpenApi\Annotations as OA;

/**
 * Class FamilyMemberController.
 */
class FamilyMemberController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = FamilyMemberResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = FamilyMemberCollection::class;

    /**
     * FamilyMemberController constructor.
     *
     * @param FamilyMemberRepository $repository
     * @param FamilyMemberPolicy $policy
     */
    public function __construct(FamilyMemberRepository $repository, FamilyMemberPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexFamilyMemberRequest::class;
        $this->actions['show'] = ShowFamilyMemberRequest::class;
        $this->actions['store'] = StoreFamilyMemberRequest::class;
        $this->actions['update'] = UpdateFamilyMemberRequest::class;
    }

    /**
     * Get eloquent query builder instance.
     *
     * @param CrudRequest $request
     *
     * @return FamilyMemberQueryBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function builder(CrudRequest $request): FamilyMemberQueryBuilder
    {
        /** @var FamilyMemberQueryBuilder $builder */
        $builder = parent::builder($request);

        return $builder->index($request->user());
    }

    /**
     * @OA\Get(
     *   path="/api/family-members",
     *   summary="Index family members.",
     *   operationId="indexFamilyMembers",
     *   security={{"bearerAuth": {}}},
     *   tags={"family-members"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/FamilyMemberIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index family members response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexFamilyMemberResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/family-members/{id}",
     *   summary="Show family member by id.",
     *   operationId="showFamilyMember",
     *   security={{"bearerAuth": {}}},
     *   tags={"family-members"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the family member."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/FamilyMemberIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show family member response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowFamilyMemberResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/family-members",
     *   summary="Store family member.",
     *   operationId="storeFamilyMember",
     *   security={{"bearerAuth": {}}},
     *   tags={"family-members"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store family member request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreFamilyMemberRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create family member response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreFamilyMemberResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/family-members/{id}",
     *   summary="Update family member.",
     *   operationId="updateFamilyMember",
     *   security={{"bearerAuth": {}}},
     *   tags={"family-members"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the family member."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update family member request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateFamilyMemberRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Update family member response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateFamilyMemberResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/family-members/{id}",
     *   summary="Delete family member.",
     *   operationId="destroyFamilyMember",
     *   security={{"bearerAuth": {}}},
     *   tags={"family-members"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the family member."),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Update family member response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyFamilyMemberResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexFamilyMemberResponse",
     *   description="Index family members response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/FamilyMember")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowFamilyMemberResponse",
     *   description="Show family member response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/FamilyMember"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="StoreFamilyMemberResponse",
     *   description="Store family member response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/FamilyMember"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="UpdateFamilyMemberResponse",
     *   description="Update family member response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/FamilyMember"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DestroyFamilyMemberResponse",
     *   description="Delete family member response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * ),
     * @OA\Schema(
     *   schema="FamilyMemberIncludes",
     *   description="Coma-separated list of inluded relations.
    Available relations: `relative`",
     *   type="string", example="relative"
     * )
     */
}
