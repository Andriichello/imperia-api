<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Ticket\IndexTicketRequest;
use App\Http\Requests\Ticket\ShowTicketRequest;
use App\Http\Resources\Ticket\TicketCollection;
use App\Http\Resources\Ticket\TicketResource;
use App\Repositories\TicketRepository;

/**
 * Class TicketController.
 */
class TicketController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = TicketResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = TicketCollection::class;

    /**
     * TicketController constructor.
     *
     * @param TicketRepository $repository
     */
    public function __construct(TicketRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexTicketRequest::class;
        $this->actions['show'] = ShowTicketRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/tickets",
     *   summary="Index tickets.",
     *   operationId="indexTickets",
     *   security={{"bearerAuth": {}}},
     *   tags={"tickets"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/TicketIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="filter[title]", required=false, in="query", example="Weekend",
     *     @OA\Schema(type="string"), description="Can be used for searches. Is partial."),
     *   @OA\Parameter(name="filter[categories]", required=false, in="query", example="2,3",
     *     @OA\Schema(type="string"), description="Coma-separated array of category ids. Limits tickets to those
     * that have at least one of given categories attached to them"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index tickets response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexTicketResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/tickets/{id}",
     *   summary="Show ticket by id.",
     *   operationId="showTicket",
     *   security={{"bearerAuth": {}}},
     *   tags={"tickets"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the tickets."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/TicketIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show tickets response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowTicketResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexTicketResponse",
     *   description="Index tickets response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Ticket")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowTicketResponse",
     *   description="Show tickets response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Ticket"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="TicketIncludes",
     *   description="Coma-separated list of inluded relations.
    Available relations: `categories`",
     *   type="string", example="categories"
     * )
     */
}
