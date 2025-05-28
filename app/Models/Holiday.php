<?php

namespace App\Models;

use App\Helpers\HolidayHelper;
use App\Queries\HolidayQueryBuilder;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\HolidayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class Holiday.
 *
 * @property int $id
 * @property int|null $restaurant_id
 * @property string $name
 * @property string|null $description
 * @property bool $repeating
 * @property Carbon $date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $closest_date
 *
 * @property Restaurant|null $restaurant
 *
 * @method static HolidayQueryBuilder query()
 * @method static HolidayFactory factory(...$parameters)
 */
class Holiday extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'date',
        'repeating',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
        'closest_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
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
     * Accessor for the closest date of the holiday.
     *
     * @return CarbonInterface|null
     */
    public function getClosestDateAttribute(): ?CarbonInterface
    {
        return (new HolidayHelper())->closest($this);
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return HolidayQueryBuilder
     */
    public function newEloquentBuilder($query): HolidayQueryBuilder
    {
        return new HolidayQueryBuilder($query);
    }
}
