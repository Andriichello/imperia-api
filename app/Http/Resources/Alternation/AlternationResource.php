<?php

namespace App\Http\Resources\Alternation;

use App\Models\Morphs\Alteration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class AlternationResource.
 *
 * @mixin Alteration
 */
class AlternationResource extends JsonResource
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
            'type' => $this->type,
            'metadata' => is_string($this->metadata) ? json_decode($this->metadata) : $this->metadata,
            'alterable_id' => $this->alterable_id,
            'alterable_type' => $this->alterable_type,
            'perform_at' => $this->perform_at,
            'performed_at' => $this->performed_at,
            'alterable' => $this->whenLoaded('alterable'),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Alternation",
     *   description="Alternation resource object",
     *   required = {"id", "type", "metadata", "alterable_id", "alterable_type",
     *     "perform_at", "performed_at"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="categories"),
     *   @OA\Property(property="metadata", type="object", nullable="true"),
     *   @OA\Property(property="alterable_id", type="integer", example=1),
     *   @OA\Property(property="alterable_type", type="string", example="products"),
     *   @OA\Property(property="perform_at", type="string", format="date-time",
     *      nullable="true", example="2022-01-12 10:00:00"),
     *   @OA\Property(property="performed_at", type="string", format="date-time",
     *       nullable="true", example=null),
     *   @OA\Property(property="alterable", oneOf={
     *      @OA\Schema(ref ="#/components/schemas/Product"),
     *      @OA\Schema(ref ="#/components/schemas/ProductVariant"),
     *    }),),
     * )
     */
}
