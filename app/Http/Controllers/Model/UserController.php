<?php

namespace App\Http\Controllers\Model;

use App\Enums\UserRole;
use App\Http\Controllers\CrudController;
use App\Http\Requests\CrudRequest;
use App\Http\Requests\User\DestroyUserRequest;
use App\Http\Requests\User\IndexUserRequest;
use App\Http\Requests\User\MeUserRequest;
use App\Http\Requests\User\RestoreUserRequest;
use App\Http\Requests\User\ShowUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Policies\UserPolicy;
use App\Queries\UserQueryBuilder;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

/**
 * Class UserController.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = UserResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = UserCollection::class;

    /**
     * UserController constructor.
     *
     * @param UserRepository $repository
     * @param UserPolicy $policy
     */
    public function __construct(UserRepository $repository, UserPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexUserRequest::class;
        $this->actions['show'] = ShowUserRequest::class;
        $this->actions['store'] = StoreUserRequest::class;
        $this->actions['update'] = UpdateUserRequest::class;
        $this->actions['destroy'] = DestroyUserRequest::class;
        $this->actions['restore'] = RestoreUserRequest::class;
    }

    /**
     * Get eloquent query builder instance.
     *
     * @param CrudRequest $request
     *
     * @return UserQueryBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function builder(CrudRequest $request): UserQueryBuilder
    {
        $user = $request->user();
        /** @var UserQueryBuilder $builder */
        $builder = parent::builder($request);

        if ($user->isManager()) {
            return $builder->whereWrapped(function (UserQueryBuilder $query) use ($user) {
                $query->onlyRoles(UserRole::Customer)
                    ->orWhere('user_id', $user->id);
            });
        }

        if ($user->isCustomer()) {
            return $builder->where('user_id', $user->id);
        }

        return $builder;
    }

    /**
     * Get currently logged-in user.
     *
     * @param MeUserRequest $request
     *
     * @return JsonResponse
     */
    public function me(MeUserRequest $request): JsonResponse
    {
        return $this->asResourceResponse($request->user());
    }

    /**
     * @OA\Get  (
     *   path="/api/users/me",
     *   summary="Get currently logged user.",
     *   operationId="me",
     *   security={{"bearerAuth": {}}},
     *   tags={"users"},
     *
     *   @OA\Response(
     *     response=200,
     *     description="Get me response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/MeResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/users",
     *   summary="Index users.",
     *   operationId="indexUsers",
     *   security={{"bearerAuth": {}}},
     *   tags={"users"},
     *
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index users response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexUserResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/users/{id}",
     *   summary="Show user by id.",
     *   operationId="showUser",
     *   security={{"bearerAuth": {}}},
     *   tags={"users"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the user."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show user response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowUserResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/user/{id}",
     *   summary="Update user.",
     *   operationId="updateUser",
     *   security={{"bearerAuth": {}}},
     *   tags={"users"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the user."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update user request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateUserRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Update user response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateUserResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="MeResponse",
     *   description="Get me response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/User"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="IndexUserResponse",
     *   description="Index users response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/User")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowUserResponse",
     *   description="Show user response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/User"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="UpdateUserResponse",
     *   description="Update user response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/User"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
