<?php

namespace App\Http\Resources\Dish;

use App\Models\DishVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DishVariantResource.
 *
 * @mixin DishVariant
 */
class DishVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'dish_id' => $this->dish_id,
            'price' => $this->price,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'calories' => $this->calories,
            'preparation_time' => $this->preparation_time,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="DishVariant",
     *   description="Dish variant resource object",
     *   required = {"id", "dish_id", "type",
     *     "price", "weight", "weight_unit", "calories", "preparation_time"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="dish_id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="dish-variants"),
     *   @OA\Property(property="price", type="float", example=15.99),
     *   @OA\Property(property="weight", type="string", nullable=true, example="120"),
     *   @OA\Property(property="weight_unit", type="string", nullable=true, example="g"),
     *   @OA\Property(property="calories", type="integer", nullable=true, example=500),
     *   @OA\Property(property="preparation_time", type="integer", nullable=true, example=30),
     * )
     */
}
