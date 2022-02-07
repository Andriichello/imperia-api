<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductRepository.
 */
class ProductRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Product::class;
}
