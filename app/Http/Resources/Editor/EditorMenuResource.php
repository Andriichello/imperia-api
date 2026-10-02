<?php

namespace App\Http\Resources\Editor;

use App\Models\DishMenu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class EditorMenuResource.
 *
 * @mixin DishMenu
 */
class EditorMenuResource extends JsonResource
{
    use FormatsTranslations;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'slug' => $this->slug,
            'title' => $this->translations($this->resource, 'title'),
            'description' => $this->translations($this->resource, 'description'),
            'is_hidden' => $this->is_hidden,
            'archived' => (bool) $this->archived,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'popularity' => $this->popularity,
            'categories' => EditorCategoryResource::collection($this->whenLoaded('categories')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorMenu",
     *   description="Menu with its categories, hidden and archived ones included.",
     *   required={"id", "restaurant_id", "slug", "title", "description", "is_hidden", "archived",
     *     "archived_at", "popularity"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="restaurant_id", type="integer", example=1),
     *   @OA\Property(property="slug", type="string", nullable=true, example="main"),
     *   @OA\Property(property="title", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="description", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="is_hidden", type="boolean", example=false),
     *   @OA\Property(property="archived", type="boolean", example=false),
     *   @OA\Property(property="archived_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="popularity", type="integer", nullable=true, example=3,
     *     description="Order of the menus, from the highest."),
     *   @OA\Property(property="categories", type="array",
     *     @OA\Items(ref="#/components/schemas/EditorCategory")),
     * ),
     */
}
