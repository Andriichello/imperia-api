<?php

namespace App\Http\Resources\Dish;

use App\Http\Resources\Media\MediaCollection;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class DishResource.
 *
 * @mixin Dish
 * @property Dish $resource
 */
class DishResource extends JsonResource
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
            'menu_id' => $this->menu_id,
            'category_id' => $this->category_id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'calories' => $this->calories,
            'preparation_time' => $this->preparation_time,
            'badge' => $this->badge,
            'archived' => $this->archived,
            'popularity' => $this->popularity,
            'metadata' => $this->metadata,
            'variants' => new DishVariantCollection($this->variants),
            'menu' => new DishMenuResource($this->whenLoaded('menu')),
            'category' => new DishCategoryResource($this->whenLoaded('category')),
            'media' => new MediaCollection($this->whenLoaded('media')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Dish",
     *   description="Dish resource object",
     *   required = {"id", "menu_id", "category_id", "type",
     *     "slug", "title", "description",
     *     "price", "weight", "weight_unit",
     *     "calories", "preparation_time", "badge",
     *     "archived", "popularity", "metadata",
     *     "variants"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="menu_id", type="integer", example=1),
     *   @OA\Property(property="category_id", type="integer", nullable=true, example=1),
     *   @OA\Property(property="type", type="string", example="dishes"),
     *   @OA\Property(property="slug", type="string", nullable=true, example="margarita"),
     *   @OA\Property(property="title", type="string", example="Margarita"),
     *   @OA\Property(property="description", type="string", nullable=true, example="Some text..."),
     *   @OA\Property(property="price", type="float", example=15.99),
     *   @OA\Property(property="weight", type="string", nullable=true, example="120"),
     *   @OA\Property(property="weight_unit", type="string", nullable=true, example="g"),
     *   @OA\Property(property="calories", type="integer", nullable=true, example=500),
     *   @OA\Property(property="preparation_time", type="integer", nullable=true, example=30),
     *   @OA\Property(property="badge", type="string", nullable=true, example="New"),
     *   @OA\Property(property="archived", type="boolean", example=false),
     *   @OA\Property(property="popularity", type="integer", nullable=true, example=1),
     *   @OA\Property(property="metadata", type="object", nullable=true),
     *   @OA\Property(property="variants", type="array", @OA\Items(ref ="#/components/schemas/DishVariant")),
     *   @OA\Property(property="menu", ref="#/components/schemas/DishMenu"),
     *   @OA\Property(property="category", ref="#/components/schemas/DishCategory"),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     * )
     */
}
