<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomerRepository.
 */
class CustomerRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Customer::class;
}
