<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class ProductCollection.
 */
class ProductVariantCollection extends ResourceCollection
{
    public $collects = ProductVariantResource::class;
}
