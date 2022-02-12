<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ServiceRepository.
 */
class ServiceRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Service::class;
}
