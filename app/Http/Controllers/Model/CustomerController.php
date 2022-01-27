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
    protected string $resourceCollectionClass = CustomerCollection::class;

    /**
     * CustomerController constructor.
     *
     * @var CustomerRepository
     */
    public function __construct(CustomerRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexCustomerRequest::class;
        $this->actions['show'] = ShowCustomerRequest::class;
        $this->actions['store'] = StoreCustomerRequest::class;
        $this->actions['update'] = UpdateCustomerRequest::class;
    }
}
