<?php

namespace App\Models;

use App\Helpers\ScheduleHelper;
use App\Models\Interfaces\ArchivableInterface;
use App\Models\Traits\ArchivableTrait;
use App\Queries\ScheduleQueryBuilder;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class Schedule.
 *
 * @property int $id
 * @property int|null $restaurant_id
 * @property string $weekday
 * @property int $beg_hour
 * @property int $beg_minute
 * @property int $end_hour
 * @property int $end_minute
 * @property bool $archived
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $begs_in
 * @property int|null $ends_in
 * @property bool $is_cross_date
 * @property CarbonInterface $closest_date
 *
 * @property Restaurant|null $restaurant
 *
 * @method static ScheduleQueryBuilder query()
 * @method static ScheduleFactory factory(...$parameters)
 */
class Schedule extends BaseModel implements
    ArchivableInterface
{
    use HasFactory;
    use ArchivableTrait {
        bootArchivableTrait as baseBootArchivableTrait;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'restaurant_id',
        'weekday',
        'beg_hour',
        'beg_minute',
        'end_hour',
        'end_minute',
        'archived',
    ];

    protected $appends = [
        'type',
        'begs_in',
        'ends_in',
        'closest_date',
        'is_cross_date',
    ];

    /**
     * Restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id', 'id');
    }

    /**
     * Accessor for value, which shows if schedule crosses a date.
     *
     * @return bool
     * @SuppressWarnings(PHPMD)
     */
    public function getIsCrossDateAttribute(): bool
    {
        return $this->beg_hour > $this->end_hour
            || ($this->beg_hour && $this->beg_hour === $this->end_hour);
    }

    /**
     * Accessor for value, which shows if schedule is working next.
     *
     * @return int|null
     */
    public function getBegsInAttribute(): ?int
    {
        $closest = $this->closest_date;
        if (!$closest->isFuture()) {
            return null;
        }

        return $closest->diffInMinutes();
    }

    /**
     * Accessor for value, which shows if schedule is currently working.
     *
     * @return int|null
     */
    public function getEndsInAttribute(): ?int
    {
        $closest = $this->closest_date
            ->setTime($this->end_hour, $this->end_minute);

        if ($this->is_cross_date && $closest->is($this->weekday)) {
            $closest->addDay();
        }

        return $closest->diffInMinutes();
    }

    /**
     * Get date that is the closest one for this schedule.
     *
     * @return CarbonInterface
     */
    public function getClosestDateAttribute(): CarbonInterface
    {
        return (new ScheduleHelper())->closest($this);
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return ScheduleQueryBuilder
     */
    public function newEloquentBuilder($query): ScheduleQueryBuilder
    {
        return new ScheduleQueryBuilder($query);
    }

    /**
     * Boot archivable trait.
     *
     * @return void
     */
    public static function bootArchivableTrait(): void
    {
        // do not add ArchivableTrait
    }
}
