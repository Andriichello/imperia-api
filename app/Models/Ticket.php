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
use App\Queries\TicketQueryBuilder;
use Carbon\Carbon;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;

/**
 * Class Space.
 *
 * @property string $title
 * @property string|null $description
 * @property float $price
 * @property bool $archived
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Restaurant[]|Collection $restaurants
 *
 * @method static TicketQueryBuilder query()
 * @method static TicketFactory factory(...$parameters)
 */
class Ticket extends BaseModel implements
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
        'title',
        'description',
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
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'media',
        'categories',
        'restaurants',
    ];

    /**
     * Restaurants associated with the model.
     *
     * @return BelongsToMany
     */
    public function restaurants(): BelongsToMany
    {
        return $this->belongsToMany(Restaurant::class, 'restaurant_ticket');
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return TicketQueryBuilder
     */
    public function newEloquentBuilder($query): TicketQueryBuilder
    {
        return new TicketQueryBuilder($query);
    }
}
