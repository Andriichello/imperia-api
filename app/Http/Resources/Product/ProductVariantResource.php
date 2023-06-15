<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Media\MediaCollection;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class ProductResource.
 *
 * @mixin ProductVariant
 */
class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'product_id' => $this->product_id,
            'price' => $this->price,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="ProductVariant",
     *   description="Product variant resource object",
     *   required = {"id", "type", "product_id", "price", "weight", "weight_unit"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="product_id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="product-variants"),
     *   @OA\Property(property="price", type="float", example=15.99),
     *   @OA\Property(property="weight", type="float", example=120),
     *   @OA\Property(property="weight_unit", type="string", example="g", enum={"g", "kg", "ml", "l", "cm"}),
     * )
     */
}
