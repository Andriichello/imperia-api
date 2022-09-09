<?php

namespace App\Repositories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HolidayRepository.
 */
class HolidayRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Holiday::class;
}
