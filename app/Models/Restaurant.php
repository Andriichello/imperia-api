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
use Database\Factories\RestaurantFactory;
use Illuminate\Database\Eloquent\Builder;
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
            ->onlyDefaults();
    }

    /**
     * Holidays are relevant for the restaurant (for one year).
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
     * @param DatabaseBuilder $query
     *
     * @return RestaurantQueryBuilder
     */
    public function newEloquentBuilder($query): RestaurantQueryBuilder
    {
        return new RestaurantQueryBuilder($query);
    }
}
