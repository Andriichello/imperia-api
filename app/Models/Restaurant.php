<?php

namespace App\Models;

use App\Enums\Weekday;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\RestaurantQueryBuilder;
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
     * @return Builder
     */
    public function defaultSchedules(): Builder
    {
        return Schedule::query()
            ->onlyDefaults();
    }

    /**
     * Schedules that restaurant operates on.
     *
     * @param string ...$weekdays
     *
     * @return Collection
     */
    public function operativeSchedules(string ...$weekdays): Collection
    {
        $defaults = $this->defaultSchedules()
            ->when(count($weekdays), fn(Builder $query) => $query->whereIn('weekday', $weekdays))
            ->get();

        $specifics = $this->schedules()
            ->when(count($weekdays), fn(Builder $query) => $query->whereIn('weekday', $weekdays))
            ->get();

        return $defaults->map(function (Schedule $default) use ($specifics) {
            $specific = $specifics->first(
                fn(Schedule $schedule) => $schedule->weekday === $default->weekday
            );

            return $specific ?? $default;
        });
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
