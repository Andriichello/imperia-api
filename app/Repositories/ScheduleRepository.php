<?php

namespace App\Repositories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ScheduleRepository.
 */
class ScheduleRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Schedule::class;
}
