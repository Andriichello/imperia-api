<?php

namespace App\Http\Resources\Editor;

use App\Models\RestaurantNote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class EditorNoteResource.
 *
 * @mixin RestaurantNote
 */
class EditorNoteResource extends JsonResource
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
            'text' => $this->translations($this->resource, 'text'),
            'is_hidden' => $this->is_hidden,
            'order' => $this->order,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorNote",
     *   description="Note of a restaurant.",
     *   required={"id", "text", "is_hidden", "order"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="text", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="is_hidden", type="boolean", example=false),
     *   @OA\Property(property="order", type="integer", example=1),
     * ),
     */
}
