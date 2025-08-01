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
use App\Queries\ServiceQueryBuilder;
use Carbon\Carbon;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;

/**
 * Class Service.
 *
 * @property int|null $restaurant_id
 * @property string|null $slug
 * @property string $title
 * @property string|null $description
 * @property float $once_paid_price
 * @property float $hourly_paid_price
 * @property bool $archived
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Restaurant[]|Collection $restaurants
 *
 * @method static ServiceQueryBuilder query()
 * @method static ServiceFactory factory(...$parameters)
 */
class Service extends BaseModel implements
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
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
        'slug',
        'title',
        'description',
        'once_paid_price',
        'hourly_paid_price',
        'archived',
        'popularity',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'once_paid_price' => 'float',
        'hourly_paid_price' => 'float',
    ];

    /**
     * Array of column names changes of which should be logged.
     *
     * @var array
     */
    protected array $logFields = [
        'once_paid_price',
        'hourly_paid_price',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
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
     * @return ServiceQueryBuilder
     */
    public function newEloquentBuilder($query): ServiceQueryBuilder
    {
        return new ServiceQueryBuilder($query);
    }
}
