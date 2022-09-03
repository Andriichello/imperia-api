<?php

namespace App\Models;

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
 * @property string $name
 * @property string|null $description
 * @property int|null $restaurant_id
 * @property int $day
 * @property int|null $month
 * @property int|null $year
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'day',
        'month',
        'year',
        'restaurant_id',
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
     * Determine if holiday has the same date.
     *
     * @param CarbonInterface|int $day
     *
     * @return bool
     */
    public function sameDay(CarbonInterface|int $day): bool
    {
        return $this->day === (is_int($day) ? $day : $day->day);
    }

    /**
     * Determine if holiday has the same month.
     *
     * @param CarbonInterface|int $month
     *
     * @return bool
     */
    public function sameMonth(CarbonInterface|int $month): bool
    {
        return $this->month === (is_int($month) ? $month : $month->month);
    }

    /**
     * Determine if holiday has the same year.
     *
     * @param CarbonInterface|int $year
     *
     * @return bool
     */
    public function sameYear(CarbonInterface|int $year): bool
    {
        return $this->year === (is_int($year) ? $year : $year->year);
    }

    /**
     * Determine if holiday is relevant on given date.
     *
     * @param CarbonInterface $date
     *
     * @return bool
     */
    public function relevantOn(CarbonInterface $date): bool
    {
        return $this->sameDay($date) && $this->sameMonth($date) && $this->sameYear($date);
    }

    /**
     * Determine if holiday is relevant from given date (included).
     *
     * @param CarbonInterface $date
     *
     * @return bool
     */
    public function relevantFrom(CarbonInterface $date): bool
    {
        $first = (!$this->day || $this->day >= $date->day)
            && (!$this->month || $this->month === $date->month)
            && (!$this->year || $this->year >= $date->year);
        $second = (!$this->month || $this->month > $date->month)
            && (!$this->year || $this->year >= $date->year);
        $third = !$this->year || $this->year > $date->year;

        return $first || $second || $third;
    }

    /**
     * Determine if holiday is relevant until the given date (included).
     *
     * @param CarbonInterface $date
     *
     * @return bool
     */
    public function relevantUntil(CarbonInterface $date): bool
    {
        $first = (!$this->day || $this->day <= $date->day)
            && (!$this->month || $this->month === $date->month)
            && (!$this->year || $this->year <= $date->year);
        $second = (!$this->month || $this->month < $date->month)
            && (!$this->year || $this->year <= $date->year);
        $third = !$this->year || $this->year < $date->year;

        return $first || $second || $third;
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
