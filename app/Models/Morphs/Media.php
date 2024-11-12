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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Class Media.
 *
 * @property int|null $restaurant_id
 * @property int|null $original_id
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
 * @property string $full_path
 * @property string|null $mime
 * @property int|null $order
 *
 * @property Media|null $original
 * @property Media[]|Collection $variants
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
        'original_id',
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
     * List of relationship names.
     *
     * @var array
     */
    protected array $relationships = [
        'mediables',
        'original',
        'variants',
        'restaurant',
    ];

    /**
     * Related original.
     *
     * @return HasMany
     */
    public function mediables(): HasMany
    {
        return $this->hasMany(Mediable::class, 'media_id', 'id');
    }

    /**
     * Related original media.
     *
     * @return BelongsTo
     */
    public function original(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'original_id', 'id');
    }

    /**
     * Related variants.
     *
     * @return HasMany
     */
    public function variants(): HasMany
    {
        return $this->hasMany(Media::class, 'original_id', 'id');
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
     * Accessor for Media mime type.
     *
     * @return string|null
     */
    public function getMimeAttribute(): ?string
    {
        return $this->attributes['extension'];
    }

    /**
     * Accessor for Media extension.
     *
     * @return string|null
     */
    public function getExtensionAttribute(): ?string
    {
        return extensionOfMime($this->attributes['extension'])
            ?? $this->attributes['extension'];
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
     * Accessor for the full path of the file.
     */
    protected function getFullPathAttribute(): string
    {
        return Str::of($this->folder)
            ->beforeLast($this->name)
            ->finish('/')
            ->append($this->name)
            ->value();
    }

    /**
     * Get media in a temporary file.
     *
     * @return resource|false Temporary file.
     */
    public function asTemporaryFile(): mixed
    {
        $body = Http::get($this->url)->body();

        $file = tmpfile();
        fwrite($file, $body);

        return $file;
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
        if ($disk === 'uploads') {
            return Str::of(env('GOOGLE_CLOUD_URL'))
                ->trim()
                ->finish('/')
                ->append(env('GOOGLE_CLOUD_BUCKET'))
                ->rtrim('/')
                ->value();
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
