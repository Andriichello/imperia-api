<?php

namespace App\Http\Resources\Media;

use App\Models\Morphs\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
     * @param  Request  $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => 'media',
            'name' => $this->name,
            'file_name' => $this->file_name,
            'file_type' => $this->type,
            'mime_type' => $this->mime_type,
            'preview_url' => $this->preview_url,
            'original_url' => $this->original_url,
            'order_column' => $this->order_column,
            'collection_name' => $this->collection_name,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Media",
     *   description="Media resource object",
     *   required = {"id", "type", "name", "file_name", "file_type", "mime_type",
     *     "preview_url", "original_url", "order_column", "collection_name"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="media"),
     *   @OA\Property(property="name", type="string", example="drinks"),
     *   @OA\Property(property="file_name", type="string", example="drinks.svg"),
     *   @OA\Property(property="file_type", type="string", example="svg"),
     *   @OA\Property(property="mime_type", type="string", example="image/svg+xml"),
     *   @OA\Property(property="preview_url", type="string", example=""),
     *   @OA\Property(property="original_url", type="string", example="http://localhost/storage/drinks.svg"),
     *   @OA\Property(property="order_column", type="integer", example="1"),
     *   @OA\Property(property="collection_name", type="string", example="images"),
     * )
     */
}
