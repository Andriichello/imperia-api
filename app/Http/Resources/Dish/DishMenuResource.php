<?php

namespace App\Http\Resources\Dish;

use App\Http\Resources\Media\MediaCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Models\DishMenu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class DishMenuResource.
 *
 * @mixin DishMenu
 * @property DishMenu $resource
 */
class DishMenuResource extends JsonResource
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
            'restaurant_id' => $this->restaurant_id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'archived' => $this->archived,
            'popularity' => $this->popularity,
            'metadata' => $this->metadata,
            'type' => $this->type,
            'dishes' => new DishCollection($this->whenLoaded('dishes')),
            'categories' => new DishCategoryCollection($this->whenLoaded('categories')),
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
            'media' => new MediaCollection($this->whenLoaded('media')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="DishMenu",
     *   description="Dish menu resource object",
     *   required = {"id", "restaurant_id", "type",
     *     "slug", "title", "description",
     *     "archived", "popularity", "metadata"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="restaurant_id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="dish-menus"),
     *   @OA\Property(property="slug", type="string", nullable=true, example="lunch-menu"),
     *   @OA\Property(property="title", type="string", example="Lunch Menu"),
     *   @OA\Property(property="description", type="string", nullable=true, example="Available from 12pm to 3pm"),
     *   @OA\Property(property="archived", type="boolean", example=false),
     *   @OA\Property(property="popularity", type="integer", nullable=true, example=1),
     *   @OA\Property(property="metadata", type="object", nullable=true),
     *   @OA\Property(property="dishes", type="array", @OA\Items(ref ="#/components/schemas/Dish")),
     *   @OA\Property(property="categories", type="array", @OA\Items(ref ="#/components/schemas/DishCategory")),
     *   @OA\Property(property="restaurant", ref="#/components/schemas/Restaurant"),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     * )
     */
}
