<?php

namespace App\Repositories;

use App\Models\RestaurantReview;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RestaurantRepository.
 */
class RestaurantReviewRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = RestaurantReview::class;
}
