<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Traits\JsonFieldTrait;
use App\Queries\MediaQueryBuilder;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class Media.
 *
 * @property int $id
 * @property string $title
 * @property Carbon $created
 * @property string $type
 * @property string $folder
 * @property string $name
 * @property boolean $private
 * @property boolean $lp
 * @property string|null $options
 *
 * @property string|null $url
 * @property string|null $path
 *
 * @method static MediaQueryBuilder query()
 */
class Media extends BaseModel
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

    /**
     * Media url accessor.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        $url = config('nova-media-library.url', '');
        return $url . $this->path;
    }

    /**
     * Media path accessor.
     *
     * @return string
     */
    public function getPathAttribute(): string
    {
        $base = config('nova-media-library.folder', '');
        $base = ltrim($base, '/public');

        return '/' . ltrim($base . $this->folder . $this->name, '/');
    }
}
