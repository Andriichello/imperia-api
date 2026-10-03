<?php

namespace App\Http\Resources\Editor;

use App\Models\MenuVersionChange;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class EditorVersionChangeResource.
 *
 * @mixin MenuVersionChange
 */
class EditorVersionChangeResource extends JsonResource
{
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
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'parent_id' => $this->parent_id,
            'is_new' => $this->isNew(),
            // an empty JSON object, not a list, when there are none (the column reorders the keys)
            'fields' => (object) array_map(
                fn (array $values) => ['live' => $values['live'] ?? null, 'new' => $values['new'] ?? null],
                $this->fields ?? []
            ),
            'changes_count' => $this->countChanges(),
            'label' => $this->resource->label,
            'conflicts' => $this->when(
                $this->resource->conflicts !== null,
                fn () => (object) $this->resource->conflicts
            ),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorVersionChange",
     *   description="Change of an item in a version: each field's live value when it was planned, and the new one.",
     *   required={"id", "target_type", "target_id", "parent_id", "is_new", "fields", "changes_count", "label"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="target_type", type="string", example="dish-variants",
     *     enum={"restaurants", "restaurant-notes", "dish-menus", "dish-categories", "dishes", "dish-variants"}),
     *   @OA\Property(property="target_id", type="integer", nullable=true, example=12,
     *     description="None for a new item."),
     *   @OA\Property(property="parent_id", type="integer", nullable=true, example=null,
     *     description="Where a new item goes."),
     *   @OA\Property(property="is_new", type="boolean", example=false),
     *   @OA\Property(property="fields", type="object", example={"price": {"live": 140, "new": 150}},
     *     additionalProperties=@OA\Schema(type="object", required={"live", "new"},
     *       @OA\Property(property="live"),
     *       @OA\Property(property="new"))),
     *   @OA\Property(property="changes_count", type="integer", example=1),
     *   @OA\Property(property="label", ref="#/components/schemas/EditorVersionLabel"),
     *   @OA\Property(property="conflicts", type="object",
     *     description="Only on the version's page: the item is gone, or live values, which changed since.",
     *     @OA\Property(property="missing", type="boolean", example=false),
     *     @OA\Property(property="fields", type="object", example={"price": 145})),
     * ),
     * @OA\Schema(
     *   schema="EditorVersionLabel",
     *   description="The changed item, in the restaurant's default language. None, when it's gone.",
     *   required={"name", "size", "path"},
     *   nullable=true,
     *   @OA\Property(property="name", type="string", nullable=true, example="Chicken broth"),
     *   @OA\Property(property="size", type="object", nullable=true, required={"weight", "weight_unit"},
     *     description="Of a dish's size.",
     *     @OA\Property(property="weight", type="string", example="350"),
     *     @OA\Property(property="weight_unit", type="string", nullable=true, example="g")),
     *   @OA\Property(property="path", type="array", @OA\Items(type="string"), example={"Main menu", "Soups"}),
     * ),
     */
}
