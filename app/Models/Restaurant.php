<?php

namespace App\Models;

use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\HolidayQueryBuilder;
use App\Queries\RestaurantQueryBuilder;
use App\Queries\ScheduleQueryBuilder;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\RestaurantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;

/**
 * Class Restaurant.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $country
 * @property string $city
 * @property string $place
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Schedule[]|Collection $schedules
 * @property Schedule[]|Collection $defaultSchedules
 * @property Schedule[]|Collection $operativeSchedules
 * @property Schedule[]|Collection $holidays
 * @property Schedule[]|Collection $defaultHolidays
 * @property Schedule[]|Collection $relevantHolidays
 * @property Schedule[]|Collection $closestHolidays
 *
 * @method static RestaurantQueryBuilder query()
 * @method static RestaurantFactory factory(...$parameters)
 */
class Restaurant extends BaseModel implements
    MediableInterface,
    SoftDeletableInterface
{
    use HasFactory;
    use MediableTrait;
    use SoftDeletableTrait;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'metadata' => '{}',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'slug',
        'name',
        'country',
        'city',
        'place',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'schedules',
    ];

    /**
     * Banquets associated with the model.
     *
     * @return HasMany
     */
    public function banquets(): HasMany
    {
        return $this->hasMany(Banquet::class, 'restaurant_id', 'id');
    }

    /**
     * Schedules associated with the model.
     *
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'restaurant_id', 'id');
    }

    /**
     * Schedules that are default for all restaurants,
     * if there is no specific ones specified.
     *
     * @return ScheduleQueryBuilder
     */
    public function defaultSchedules(): ScheduleQueryBuilder
    {
        return Schedule::query()
            ->onlyDefaults();
    }

    /**
     * Load default schedules and set it into $defaultSchedules.
     *
     * @return Collection
     */
    public function loadDefaultSchedules(): Collection
    {
        /** @var Collection $schedules */
        $schedules = $this->defaultSchedules()
            ->get()->sortBy('begs_in');

        return $this->defaultSchedules = $schedules;
    }

    /**
     * Accessor for schedules that are default for all restaurants,
     * if there is no specific ones specified.
     *
     * @return Collection
     */
    public function getDefaultSchedulesAttribute(): Collection
    {
        return $this->defaultSchedules ?? $this->loadDefaultSchedules();
    }

    /**
     * Load operative schedules and set it into $operativeSchedules.
     *
     * @return Collection
     */
    public function loadOperativeSchedules(): Collection
    {
        $schedules = $this->defaultSchedules
            ->map(function (Schedule $default) {
                $specific = $this->schedules->first(
                    fn(Schedule $schedule) => $schedule->weekday === $default->weekday
                );

                return $specific ?? $default;
            });

        return $this->operativeSchedules = $schedules->sortBy('begs_in');
    }

    /**
     * Accessor for schedules that restaurant operates on.
     *
     * @return Collection
     */
    public function getOperativeSchedulesAttribute(): Collection
    {
        return $this->operativeSchedules ?? $this->loadOperativeSchedules();
    }

    /**
     * Holidays associated with the model.
     *
     * @return HasMany
     */
    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class, 'restaurant_id', 'id');
    }

    /**
     * Holidays that are default for all restaurants,
     * if there is no specific ones specified.
     *
     * @return HolidayQueryBuilder
     */
    public function defaultHolidays(): HolidayQueryBuilder
    {
        return Holiday::query()
            ->onlyDefaults()
            ->relevantFrom(now())
            ->relevantUntil(now()->addYear());
    }

    /**
     * Load default holidays and set it into $defaultHolidays.
     *
     * @return Collection
     */
    public function loadDefaultHolidays(): Collection
    {
        /** @var Collection $holidays */
        $holidays = $this->defaultHolidays()->get();

        return $this->defaultHolidays = $holidays;
    }

    /**
     * Accessor for holidays that are default for all restaurants (for one year).
     *
     * @return Collection
     */
    public function getDefaultHolidaysAttribute(): Collection
    {
        return $this->defaultHolidays ?? $this->loadDefaultHolidays();
    }

    /**
     * Holidays that are relevant for the restaurant (for one year).
     *
     * @return HolidayQueryBuilder
     */
    public function relevantHolidays(): HolidayQueryBuilder
    {
        return Holiday::query()
            ->withRestaurant($this->id, null)
            ->relevantFrom(now())
            ->relevantUntil(now()->addYear());
    }

    /**
     * Load holidays that are relevant for the restaurant
     * and set it into $relevantHolidays.
     *
     * @return Collection
     */
    public function loadRelevantHolidays(): Collection
    {
        /** @var Collection $holidays */
        $holidays = $this->relevantHolidays()->get();

        return $this->relevantHolidays = $holidays->sortBy('closest_date');
    }

    /**
     * Accessor for holidays that are relevant for the restaurants (for one year).
     *
     * @return Collection
     */
    public function getRelevantHolidaysAttribute(): Collection
    {
        return $this->relevantHolidays ?? $this->loadRelevantHolidays();
    }

    /**
     * Holidays that are relevant for the restaurant (for one year).
     *
     * @param int $days
     * @param CarbonInterface|null $from
     *
     * @return HolidayQueryBuilder
     */
    public function closestHolidays(int $days = 7, ?CarbonInterface $from = null): HolidayQueryBuilder
    {
        $sub = Holiday::query();
        $date = ($from ?? now());

        for ($i = 0; $i < $days; $i++) {
            $sub->orWhereWrapped(function (HolidayQueryBuilder $query) use ($date, $i) {
                $query->relevantOn($date->clone()->addDays($i));
            });
        }

        return Holiday::query()
            ->withRestaurant($this->id, null)
            ->addWrappedWhereQuery($sub);
    }

    /**
     * Load holidays that are closest for the next week
     * and set it into $closestHolidays.
     *
     * @return Collection
     */
    public function loadClosestHolidays(): Collection
    {
        /** @var Collection $holidays */
        $holidays = $this->closestHolidays()->get();

        return $this->closestHolidays = $holidays->sortBy('closest_date');
    }

    /**
     * Accessor for holidays that are the closest ones (for one week).
     *
     * @return Collection
     */
    public function getClosestHolidaysAttribute(): Collection
    {
        return $this->closestHolidays ?? $this->loadClosestHolidays();
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return RestaurantQueryBuilder
     */
    public function newEloquentBuilder($query): RestaurantQueryBuilder
    {
        return new RestaurantQueryBuilder($query);
    }
}
