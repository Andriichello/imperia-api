<?php

namespace App\Repositories;

use App\Helpers\MediaHelper;
use App\Models\Morphs\Media;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class MediaRepository.
 */
class MediaRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Media::class;

    /**
     * @var MediaHelper
     */
    protected MediaHelper $helper;

    public function __construct(MediaHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param array $attributes
     *
     * @return Media
     * @throws FileNotFoundException
     */
    public function create(array $attributes): Media
    {
        $disk = $attributes['disk'];
        $from = $attributes['file'];

        if ($from instanceof File || $from instanceof UploadedFile) {
            $attributes['extension'] = mime_content_type($from->getPathname());

            if (!isset($attributes['title'])) {
                $attributes['title'] = $attributes['name'];
            }

            $attributes['name'] = Media::hash($from->path());
        }

        $restaurantId = $attributes['restaurant_id'] ?? null;
        if ($restaurantId) {
            $attributes['folder'] = Str::of($attributes['folder'])
                ->finish('/')
                ->append($restaurantId)
                ->finish('/')
                ->value();
        }

        $to = Str::of($attributes['folder'])
            ->finish('/')
            ->append($attributes['name'])
            ->value();

        $media = $this->helper->store($from, $to, $disk);

        $this->fillFromAttributes($media, $attributes);
        $media->touch();

        $this->refreshMetadata($media);

        return $media;
    }

    /**
     * @param Media $original
     * @param mixed $variant
     *
     * @return Media
     * @throws FileNotFoundException
     */
    public function createVariant(Media $original, mixed $variant): Media
    {
        $attributes = [
            'original_id' => $original->id,
            'restaurant_id' => $original->restaurant_id,
            'disk' => $original->disk,
            'title' => $original->title,
            'description' => $original->description,
            'folder' => $original->folder,
        ];

        if (is_resource($variant)) {
            $attributes['extension'] = mime_content_type($path = pathOf($variant));
            $attributes['name'] = Media::hash($path);
        }

        if ($variant instanceof File || $variant instanceof UploadedFile) {
            $attributes['extension'] = mime_content_type($path = $variant->getPathname());
            $attributes['name'] = Media::hash($path);
        }

        $path = Str::of($original->folder)
            ->finish('/')
            ->append($attributes['name'])
            ->value();

        $media = $this->helper->store($variant, $path, $original->disk);

        $this->fillFromAttributes($media, $attributes);
        $media->touch();

        $this->refreshMetadata($media);

        return $media;
    }

    /**
     * @param Model|Media $model
     * @param array $attributes
     *
     * @return bool
     * @throws FileNotFoundException
     */
    public function update(Model|Media $model, array $attributes): bool
    {
        /** @var Media $model */
        $disk = data_get($attributes, 'disk', $model->disk);

        $from = data_get($attributes, 'file');
        if ($from instanceof File || $from instanceof UploadedFile) {
            $attributes['extension'] = $from->getClientMimeType();

            if (!isset($attributes['title'])) {
                $attributes['title'] = $attributes['name'];
            }

            $attributes['name'] = Media::hash($from->path());
        }

        $to = data_get($attributes, 'folder', $model->folder)
            . data_get($attributes, 'name', $model->name);

        $media = $this->helper->update($model, $from, $to, $disk);

        $this->fillFromAttributes($model, $attributes);
        return $media->touch();
    }

    /**
     * @param Model|Media $model
     *
     * @return bool
     * @throws FileNotFoundException
     */
    public function delete(Model|Media $model): bool
    {
        /* @phpstan-ignore-next-line */
        return $this->helper->delete($model, $model->disk);
    }

    /**
     * Fill media with values from attributes.
     *
     * @param Media $media
     * @param array $attributes
     *
     * @return Media
     */
    protected function fillFromAttributes(Media $media, array $attributes): Media
    {
        if (Arr::exists($attributes, 'restaurant_id') && $attributes['restaurant_id']) {
            $media->restaurant_id = $attributes['restaurant_id'];
        }
        if (Arr::exists($attributes, 'original_id') && $attributes['original_id']) {
            $media->original_id = $attributes['original_id'];
        }
        if (Arr::exists($attributes, 'title')) {
            $media->title = $attributes['title'];
        }
        if (Arr::exists($attributes, 'description')) {
            $media->description = $attributes['description'];
        }
        if (Arr::exists($attributes, 'extension')) {
            $media->extension = $attributes['extension'];
        }
        if (Arr::exists($attributes, 'metadata')) {
            $media->setJson('metadata', (array)$attributes['metadata']);
        }

        return $media;
    }

    /**
     * Refresh metadata for the given file model.
     *
     * @param Media $media
     *
     * @return bool
     */
    public function refreshMetadata(Media $media): bool
    {
        $media->setJson(
            'metadata',
            array_merge(
                $media->getJson('metadata'),
                $this->helper->metadata($media) ?? [],
            )
        );

        return $media->save();
    }
}
