<?php

namespace App\Models\Morphs;

use App\Models\Traits\JsonFieldTrait;
use App\Queries\MediaQueryBuilder;
use Carbon\Carbon;
use ClassicO\NovaMediaLibrary\Core\Model as MediaModel;
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
class Media extends MediaModel
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
