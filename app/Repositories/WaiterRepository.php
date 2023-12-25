<?php

namespace App\Repositories;

use App\Models\Waiter;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WaiterRepository.
 */
class WaiterRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Waiter::class;
}
