<?php

namespace App\Repositories;

use App\Models\Morphs\Discount;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DiscountRepository.
 */
class DiscountRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Discount::class;
}
