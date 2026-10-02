<?php

namespace App\Http\Resources\Editor;

use App\Models\DishCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class EditorCategoryResource.
 *
 * @mixin DishCategory
 */
class EditorCategoryResource extends JsonResource
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
            'menu_id' => $this->menu_id,
            'slug' => $this->slug,
            'title' => $this->translations($this->resource, 'title'),
            'description' => $this->translations($this->resource, 'description'),
            'is_hidden' => $this->is_hidden,
            'archived' => (bool) $this->archived,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'popularity' => $this->popularity,
            'dishes' => EditorDishResource::collection($this->whenLoaded('dishes')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorCategory",
     *   description="Category with its dishes, hidden and archived ones included.",
     *   required={"id", "menu_id", "slug", "title", "description", "is_hidden", "archived",
     *     "archived_at", "popularity"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="menu_id", type="integer", example=1),
     *   @OA\Property(property="slug", type="string", nullable=true, example="soups"),
     *   @OA\Property(property="title", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="description", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="is_hidden", type="boolean", example=false),
     *   @OA\Property(property="archived", type="boolean", example=false),
     *   @OA\Property(property="archived_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="popularity", type="integer", nullable=true, example=5,
     *     description="Order of the categories in the menu, from the highest."),
     *   @OA\Property(property="dishes", type="array", @OA\Items(ref="#/components/schemas/EditorDish")),
     * ),
     */
}
