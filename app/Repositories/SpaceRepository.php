<?php

namespace App\Repositories;

use App\Models\Space;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SpaceRepository.
 */
class SpaceRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Space::class;
}
