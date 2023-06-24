<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Restaurant;
use App\Queries\MediaQueryBuilder;
use Carbon\Carbon;
use Database\Factories\Morphs\MediaFactory;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;

/**
 * Class Media.
 *
 * @property int|null $restaurant_id
 * @property string $name
 * @property string $extension
 * @property string|null $title
 * @property string|null $description
 * @property string $disk
 * @property string $folder
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property string $url
 * @property int|null $order
 *
 * @property Restaurant|null $restaurant
 * @property Mediable[]|Collection $mediables
 *
 * @method static MediaQueryBuilder query()
 * @method static MediaFactory factory(...$parameters)
 */
class Media extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'restaurant_id',
        'name',
        'extension',
        'title',
        'description',
        'disk',
        'folder',
        'metadata',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
        'url',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'mediables',
        'restaurant',
    ];

    /**
     * Related mediables.
     *
     * @return HasMany
     */
    public function mediables(): HasMany
    {
        return $this->hasMany(Mediable::class, 'media_id', 'id');
    }

    /**
     * Related restaurant.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }


    /**
     * Accessor for Media url.
     *
     * @return string
     * @throws Exception
     */
    public function getUrlAttribute(): string
    {
        return $this->baseUrl($this->disk) . $this->folder . $this->name;
    }

    /**
     * Accessor for Media's order.
     *
     * @return int|null
     * @throws Exception
     */
    public function getOrderAttribute(): ?int
    {
        return data_get($this, 'pivot.order');
    }

    /**
     * Get base url for given disk.
     *
     * @param string $disk
     *
     * @return string
     * @throws Exception
     */
    public static function baseUrl(string $disk): string
    {
        $url = rtrim(env('APP_URL'), '/');

        if ($disk === 'public') {
            return $url . '/storage';
        }
        if ($disk === 'google-cloud') {
            return rtrim(env('GOOGLE_CLOUD_BUCKET_URL'), '/');
        }

        throw new Exception("No baseUrl mapping for '$disk'.");
    }

    /**
     * Get hash for file under given path.
     *
     * @param string $path
     *
     * @return string
     */
    public static function hash(string $path): string
    {
        return md5(md5_file($path) . microtime());
    }

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
