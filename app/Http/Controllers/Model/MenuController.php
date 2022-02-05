<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Menu\IndexMenuRequest;
use App\Http\Requests\Menu\ShowMenuRequest;
use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Menu\MenuResource;
use App\Repositories\MenuRepository;

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
     */
    public function __construct(MenuRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexMenuRequest::class;
        $this->actions['show'] = ShowMenuRequest::class;
    }
}
