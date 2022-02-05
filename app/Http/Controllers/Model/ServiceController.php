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
}
