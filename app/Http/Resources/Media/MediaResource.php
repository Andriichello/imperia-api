<?php

namespace App\Http\Resources\Media;

use App\Models\Morphs\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class MediaResource.
 *
 * @mixin Media
 */
class MediaResource extends JsonResource
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
            'name' => $this->name,
            'mime' => $this->mime,
            'extension' => $this->extension,
            'title' => $this->title,
            'description' => $this->description,
            'disk' => $this->disk,
            'folder' => $this->folder,
            'order' => $this->order,
            'url' => $this->url,
            'metadata' => $this->metadata,
            'variants' => new MediaCollection($this->whenLoaded('variants')),
        ];
    }

    /**
     * @OA\Schema(
     *    schema="MediaMetadata",
     *    description="Media metadata resource object",
     *    type="object"
     * ),
     *
     * @OA\Schema(
     *   schema="Media",
     *   description="Media resource object",
     *   required = {"id", "type", "name", "extension", "title",
     *     "description", "disk", "folder", "order", "url", "metadata"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="media"),
     *   @OA\Property(property="name", type="string", example="drinks.svg"),
     *   @OA\Property(property="mime", type="string", example="image/svg+xml"),
     *   @OA\Property(property="extension", type="string", example="svg"),
     *   @OA\Property(property="title", type="string", nullable=true, example="Drinks"),
     *   @OA\Property(property="description", type="string", nullable=true),
     *   @OA\Property(property="disk", type="string", nullable=true, example="public"),
     *   @OA\Property(property="folder", type="string", example="/",
     *     description="Must start and end with `/`."),
     *   @OA\Property(property="order", type="integer", nullable=true, example=1),
     *   @OA\Property(property="url", type="string", example="http://localhost/storage/drinks.svg"),
     *   @OA\Property(property="metadata", nullable=true,
     *     ref ="#/components/schemas/MediaMetadata"),
     *   @OA\Property(property="variants", type="array",
     *     @OA\Items(ref ="#/components/schemas/Media")),
     * )
     */
}
