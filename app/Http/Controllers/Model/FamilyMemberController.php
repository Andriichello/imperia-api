<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\FamilyMember\IndexFamilyMemberRequest;
use App\Http\Requests\FamilyMember\ShowFamilyMemberRequest;
use App\Http\Requests\FamilyMember\StoreFamilyMemberRequest;
use App\Http\Requests\FamilyMember\UpdateFamilyMemberRequest;
use App\Http\Resources\FamilyMember\FamilyMemberCollection;
use App\Http\Resources\FamilyMember\FamilyMemberResource;
use App\Repositories\FamilyMemberRepository;

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
    protected string $resourceCollectionClass = FamilyMemberCollection::class;

    /**
     * FamilyMemberController constructor.
     *
     * @var FamilyMemberRepository
     */
    public function __construct(FamilyMemberRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexFamilyMemberRequest::class;
        $this->actions['show'] = ShowFamilyMemberRequest::class;
        $this->actions['store'] = StoreFamilyMemberRequest::class;
        $this->actions['update'] = UpdateFamilyMemberRequest::class;
    }
}
