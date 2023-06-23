<?php

namespace App\Models;

use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Morphs\Category;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\HolidayQueryBuilder;
use App\Queries\RestaurantQueryBuilder;
use App\Queries\ScheduleQueryBuilder;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\RestaurantFactory;
use DateTimeZone;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property string $timezone
 * @property int $timezone_offset
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Banquet[]|Collection $banquets
 * @property Menu[]|Collection $menus
 * @property Space[]|Collection $spaces
 * @property Service[]|Collection $services
 * @property Ticket[]|Collection $tickets
 * @property Product[]|Collection $products
 * @property Category[]|Collection $categories
 * @property Schedule[]|Collection $schedules
 * @property Holiday[]|Collection $holidays
 * @property Holiday[]|Collection $relevantHolidays
 * @property RestaurantReview[]|Collection $reviews
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
        'timezone',
        'popularity',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'banquets',
        'menus',
        'products',
        'spaces',
        'tickets',
        'services',
        'categories',
        'schedules',
        'holidays',
        'relevantHolidays',
        'reviews',
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
     * Menus associated with the model.
     *
     * @return HasMany
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    /**
     * Products associated with the model.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Spaces associated with the model.
     *
     * @return HasMany
     */
    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }

    /**
     * Tickets associated with the model.
     *
     * @return HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Services associated with the model.
     *
     * @return HasMany
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Categories associated with the model.
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)
            ->orderBy('date');
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
     * Holidays associated with the model.
     *
     * @return HasMany
     */
    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class)
            ->orderBy('date');
    }

    /**
     * Holidays that are relevant for the restaurant (for one year).
     *
     * @return BelongsToMany
     */
    public function relevantHolidays(): BelongsToMany
    {
        return $this->holidays()
            ->where('date', '>=', now()->setTime(0, 0));
    }

    /**
     * Reviews associated with the model.
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(RestaurantReview::class, 'restaurant_id', 'id')
            ->orderByDesc('created_at');
    }

    /**
     * Get restaurant's timezone offset in minutes.
     *
     * @return int
     * @throws Exception
     */
    public function getTimezoneOffsetAttribute(): int
    {
        if (!$this->timezone || empty($this->timezone)) {
            return 0;
        }

        $timezone = new DateTimeZone($this->timezone);

        $date = Carbon::now()
            ->setTimezone($timezone);

        return $timezone->getOffset($date) / 60;
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
