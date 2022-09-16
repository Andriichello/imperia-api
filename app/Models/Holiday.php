<?php

namespace App\Models;

use App\Helpers\HolidayHelper;
use App\Queries\HolidayQueryBuilder;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\HolidayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;

/**
 * Class Holiday.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property Carbon $date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $closest_date
 *
 * @property Restaurant[]|Collection $restaurants
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
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'date',
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
     * Restaurants associated with the model.
     *
     * @return BelongsToMany
     */
    public function restaurants(): BelongsToMany
    {
        return $this->belongsToMany(Restaurant::class, 'restaurant_holiday');
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
