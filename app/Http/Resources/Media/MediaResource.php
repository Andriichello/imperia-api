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
            'type' => $this->type,
            'name' => $this->name,
            'extension' => $this->extension,
            'title' => $this->title,
            'description' => $this->description,
            'disk' => $this->disk,
            'folder' => $this->folder,
            'url' => $this->url,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Media",
     *   description="Media resource object",
     *   required = {"id", "type", "name", "extension", "title",
     *     "description", "disk", "folder", "url"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="media"),
     *   @OA\Property(property="name", type="string", example="drinks.svg"),
     *   @OA\Property(property="extension", type="string", example="svg"),
     *   @OA\Property(property="title", type="string", nullable="true", example="Drinks"),
     *   @OA\Property(property="description", type="string", nullable="true"),
     *   @OA\Property(property="disk", type="string", nullable="true", example="public"),
     *   @OA\Property(property="folder", type="string", example="/",
     *     description="Must start and end with `/`."),
     *   @OA\Property(property="url", type="string", example="http://localhost/storage/drinks.svg"),
     * )
     */
}
