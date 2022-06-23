<?php

namespace App\Models\Morphs;

use App\Models\Traits\JsonFieldTrait;
use App\Queries\MediaQueryBuilder;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * Class Media.
 *
 * @property int $id
 * @property int $model_id
 * @property string $model_type
 * @property string|null $uuid
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string|null $mime_type
 * @property string $disk
 * @property string|null $conversions_disk
 * @property int $size
 * @property array $manipulations
 * @property array $custom_properties
 * @property array $generated_conversions
 * @property array $responsive_images
 * @property int|null $order_column
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property string $type
 * @property string $extension
 * @property string $human_readable_size
 * @property string $preview_url
 * @property string $original_url
 *
 * @method static MediaQueryBuilder query()
 */
class Media extends BaseMedia
{
    use JsonFieldTrait;

    /**
     * @param DatabaseBuilder $query
     *
     * @return MediaQueryBuilder
     */
    public function newEloquentBuilder($query): MediaQueryBuilder
    {
        return new MediaQueryBuilder($query);
    }
}
