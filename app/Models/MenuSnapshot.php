<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class MenuSnapshot.
 *
 * The dishes guests see on a restaurant's pages, in one language, as a gzipped JSON file of the
 * restaurant's content version. The file is named after its content: it never changes, a change
 * of the pages gets a file of its own (see `MenuSnapshotRepository`).
 *
 * @property int $id
 * @property int $restaurant_id
 * @property string $locale
 * @property int $content_version
 * @property string $path
 * @property string $hash
 * @property int $dish_count
 * @property int $size
 * @property int $gzip_size
 * @property int $built_ms
 * @property Carbon|null $created_at
 *
 * @property Restaurant $restaurant
 */
class MenuSnapshot extends BaseModel
{
    /**
     * Snapshots never change: they've no update time.
     */
    public const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menu_snapshots';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
        'locale',
        'content_version',
        'path',
        'hash',
        'dish_count',
        'size',
        'gzip_size',
        'built_ms',
    ];

    /**
     * The restaurant, whose dishes it is.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }
}
