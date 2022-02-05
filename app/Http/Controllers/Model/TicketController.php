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
}
