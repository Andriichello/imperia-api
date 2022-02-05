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
}
