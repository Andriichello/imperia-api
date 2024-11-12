<?php

namespace App\Models;

use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\CategorizableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\CategorizableTrait;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\SpaceQueryBuilder;
use Carbon\Carbon;
use Database\Factories\SpaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class Space.
 *
 * @property int|null $restaurant_id
 * @property string|null $slug
 * @property string $title
 * @property string|null $description
 * @property float $price
 * @property int $floor
 * @property int $number
 * @property bool $archived
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Restaurant|null $restaurant
 *
 * @method static SpaceQueryBuilder query()
 * @method static SpaceFactory factory(...$parameters)
 */
class Space extends BaseModel implements
    SoftDeletableInterface,
    CategorizableInterface,
    ArchivableInterface,
    LoggableInterface,
    MediableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use CategorizableTrait;
    use ArchivableTrait;
    use LoggableTrait;
    use MediableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'restaurant_id',
        'slug',
        'title',
        'description',
        'number',
        'floor',
        'price',
        'archived',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
    ];

    /**
     * Array of column names changes of which should be logged.
     *
     * @var array
     */
    protected array $logFields = [
        'price',
    ];

    /**
     * List of relationship names.
     *
     * @var array
     */
    protected array $relationships = [
        'media',
        'categories',
        'restaurant',
    ];

    /**
     * Restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return SpaceQueryBuilder
     */
    public function newEloquentBuilder($query): SpaceQueryBuilder
    {
        return new SpaceQueryBuilder($query);
    }
}
