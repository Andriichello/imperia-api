<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Customer\IndexCustomerRequest;
use App\Http\Requests\Customer\ShowCustomerRequest;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\Customer\CustomerCollection;
use App\Http\Resources\Customer\CustomerResource;
use App\Repositories\CustomerRepository;

/**
 * Class CustomerController.
 */
class CustomerController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = CustomerResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = CustomerCollection::class;

    /**
     * CustomerController constructor.
     *
     * @param  CustomerRepository $repository
     */
    public function __construct(CustomerRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexCustomerRequest::class;
        $this->actions['show'] = ShowCustomerRequest::class;
        $this->actions['store'] = StoreCustomerRequest::class;
        $this->actions['update'] = UpdateCustomerRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/customers",
     *   summary="Index customers.",
     *   operationId="indexCustomers",
     *   security={{"bearerAuth": {}}},
     *   tags={"customers"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/CustomerIncludes")),
     *   @OA\Parameter(name="trashed", in="query",
     *     @OA\Schema(ref ="#/components/schemas/TrashedParameter")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index customers response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexCustomerResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/customers/{id}",
     *   summary="Show customer by id.",
     *   operationId="showCustomer",
     *   security={{"bearerAuth": {}}},
     *   tags={"customers"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the customer."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/CustomerIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show customer response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowCustomerResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/customers",
     *   summary="Store customer.",
     *   operationId="storeCustomer",
     *   security={{"bearerAuth": {}}},
     *   tags={"customers"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store customer request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreCustomerRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create customer response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreCustomerResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/customers/{id}",
     *   summary="Update customer.",
     *   operationId="updateCustomer",
     *   security={{"bearerAuth": {}}},
     *   tags={"customers"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the customer."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update customer request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateCustomerRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Update customer response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateCustomerResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/customers/{id}",
     *   summary="Delete customer.",
     *   operationId="destroyCustomer",
     *   security={{"bearerAuth": {}}},
     *   tags={"customers"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the customer."),
     *
     *  @OA\RequestBody(
     *     required=false,
     *     description="Delete customer request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Delete customer response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyCustomerResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/customers/{id}/restore",
     *   summary="Restore customer.",
     *   operationId="restoreCustomer",
     *   security={{"bearerAuth": {}}},
     *   tags={"customers"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the customer."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Restore customer response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/RestoreCustomerResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexCustomerResponse",
     *   description="Index customers response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Customer")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowCustomerResponse",
     *   description="Show customer response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Customer"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="StoreCustomerResponse",
     *   description="Store customer response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Customer"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="UpdateCustomerResponse",
     *   description="Update customer response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Customer"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DestroyCustomerResponse",
     *   description="Delete customer response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * ),
     * @OA\Schema(
     *   schema="RestoreCustomerResponse",
     *   description="Restore customer response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Customer"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="CustomerIncludes",
     *   description="Coma-separated list of inluded relations.
     Available relations: `comments`, `family_members`",
     *   type="string", example="family_members,comments"
     * )
     */
}
