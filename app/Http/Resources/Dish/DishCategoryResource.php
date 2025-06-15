<?php

namespace App\Http\Resources\Dish;

use App\Http\Resources\Dish\DishMenuResource;
use App\Http\Resources\Media\MediaCollection;
use App\Models\DishCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class DishCategoryResource.
 *
 * @mixin DishCategory
 * @property DishCategory $resource
 */
class DishCategoryResource extends JsonResource
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
            'slug' => $this->slug,
            'target' => $this->target,
            'title' => $this->title,
            'description' => $this->description,
            'archived' => $this->archived,
            'popularity' => $this->popularity,
            'metadata' => $this->metadata,
            'menu' => new DishMenuResource($this->whenLoaded('menu')),
            'media' => new MediaCollection($this->whenLoaded('media')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="DishCategory",
     *   description="Dish category resource object",
     *   required = {"id", "menu_id", "type",
     *     "slug", "target", "title", "description",
     *      "archived", "popularity", "metadata"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="menu_id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="dish-categories"),
     *   @OA\Property(property="slug", type="string", nullable=true, example="appetizers"),
     *   @OA\Property(property="target", type="string", nullable=true, example="dishes"),
     *   @OA\Property(property="title", type="string", example="Appetizers"),
     *   @OA\Property(property="description", type="string", nullable=true, example="Small dishes to start your meal"),
     *   @OA\Property(property="archived", type="boolean", example=false),
     *   @OA\Property(property="popularity", type="integer", nullable=true, example=1),
     *   @OA\Property(property="metadata", type="object", nullable=true),
     *   @OA\Property(property="menu", ref="#/components/schemas/DishMenu"),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     * )
     */
}
