<?php

namespace App\Repositories;

use App\Models\Banquet;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BanquetRepository.
 */
class BanquetRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Banquet::class;
}
