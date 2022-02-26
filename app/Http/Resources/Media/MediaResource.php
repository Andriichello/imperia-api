<?php

namespace App\Http\Resources\Media;

use ClassicO\NovaMediaLibrary\Core\Model as MediaModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MediaResource.
 *
 * @mixin MediaModel
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
            'id' => $this->id, // @phpstan-ignore-line
            'type' => 'media',
            'name' => $this->name, // @phpstan-ignore-line
            'title' => $this->title, // @phpstan-ignore-line
            'url' => $this->url, // @phpstan-ignore-line
            'path' => $this->path, // @phpstan-ignore-line
            'folder' => $this->folder, // @phpstan-ignore-line
            'private' => (bool) $this->private, // @phpstan-ignore-line
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Media",
     *   description="Media resource object",
     *   required = {"id", "type", "name", "title", "url", "path", "folder", "private"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="media"),
     *   @OA\Property(property="name", type="string", example="mojito-1645908379CXJyz.jpg"),
     *   @OA\Property(property="title", type="string", example="Mojito"),
     *   @OA\Property(property="url", type="string", example="http://localhost/storage/mojito-1645908379CXJyz.jpg"),
     *   @OA\Property(property="path", type="string", example="/mojito-1645908379CXJyz.jpg"),
     *   @OA\Property(property="folder", type="string", example="/"),
     *   @OA\Property(property="private", type="boolean", example="false"),
     * )
     */
}
