<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class ProductCollection.
 */
class ProductCollection extends ResourceCollection
{
    public $collects = ProductResource::class;
}
