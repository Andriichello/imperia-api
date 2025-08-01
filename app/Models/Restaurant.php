<?php

namespace App\Models;

use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Morphs\Category;
use App\Models\Morphs\Tip;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\RestaurantQueryBuilder;
use Carbon\Carbon;
use Database\Factories\RestaurantFactory;
use DateTimeZone;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string|null $location
 * @property string|null $full_address
 * @property int $timezone_offset
 * @property string|null $locale
 * @property string|null $currency
 * @property string|null $establishment
 * @property string[]|null $notes
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
 * @property Waiter[]|Collection $waiters
 * @property Tip[]|Collection $tips
 *
 * @property DishMenu[]|Collection $dishMenus
 * @property DishCategory[]|Collection $dishCategories
 * @property Dish[]|Collection $dishes
 *
 * @method static RestaurantQueryBuilder query()
 * @method static RestaurantFactory factory(...$parameters)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'name',
        'country',
        'city',
        'place',
        'phone',
        'notes',
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
        'waiters',
        'tips',
        'dishMenus',
        'dishCategories',
        'dishes',
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
        /** @phpstan-ignore-next-line */
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
        /** @phpstan-ignore-next-line */
        return $this->hasMany(Holiday::class)
            ->orderBy('date');
    }

    /**
     * Holidays that are relevant for the restaurant (for one year).
     *
     * @return HasMany
     */
    public function relevantHolidays(): HasMany
    {
        /** @phpstan-ignore-next-line */
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
        /** @phpstan-ignore-next-line */
        return $this->hasMany(RestaurantReview::class, 'restaurant_id', 'id')
            ->orderByDesc('created_at');
    }

    /**
     * Waiters associated with the model.
     *
     * @return HasMany
     */
    public function waiters(): HasMany
    {
        return $this->hasMany(Waiter::class, 'restaurant_id', 'id');
    }

    /**
     * Tips associated with the model (by the `restaurant_id`).
     *
     * @return HasMany
     */
    public function tips(): HasMany
    {
        return $this->hasMany(Tip::class, 'restaurant_id', 'id');
    }

    /**
     * Dish menus associated with the model.
     *
     * @return HasMany
     */
    public function dishMenus(): HasMany
    {
        return $this->hasMany(DishMenu::class);
    }

    /**
     * Dish categories associated with the model.
     *
     * @return HasManyThrough
     */
    public function dishCategories(): HasManyThrough
    {
        return $this->hasManyThrough(
            DishCategory::class,
            DishMenu::class,
            'restaurant_id',
            'menu_id',
            'id',
            'id'
        );
    }

    /**
     * Dish categories associated with the model.
     *
     * @return HasManyThrough
     */
    public function dishes(): HasManyThrough
    {
        return $this->hasManyThrough(
            Dish::class,
            DishMenu::class,
            'restaurant_id',
            'menu_id',
            'id',
            'id'
        );
    }

    /**
     * Accessor for the restaurant's timezone offset in minutes.
     *
     * @return Attribute
     */
    public function timezoneOffset(): Attribute
    {
        return Attribute::get(
            function () {
                if (!$this->timezone || empty($this->timezone)) {
                    return 0;
                }

                $timezone = new DateTimeZone($this->timezone);

                $date = Carbon::now()
                    ->setTimezone($timezone);

                return $timezone->getOffset($date) / 60;
            }
        );
    }

    /**
     * Accessor for the restaurant's full address.
     *
     * @return Attribute
     */
    public function fullAddress(): Attribute
    {
        return Attribute::get(
            function () {
                if (!$this->place && !$this->city && !$this->country) {
                    return null;
                }

                return implode(', ', [
                    $this->place ?? '',
                    $this->city ?? '',
                    $this->country ?? '',
                ]);
            }
        );
    }

    /**
     * Accessor for the restaurant's currency.
     *
     * @return string|null
     */
    public function getCurrencyAttribute(): ?string
    {
        return $this->getFromJson('metadata', 'currency');
    }

    /**
     * Mutator for the restaurant's currency.
     *
     * @param $currency string|null
     */
    public function setCurrencyAttribute(?string $currency): void
    {
        $this->setToJson('metadata', 'currency', $currency);
    }

    /**
     * Accessor for the restaurant's establishment type (restaurant, café, bakery...).
     *
     * @return string|null
     */
    public function getEstablishmentAttribute(): ?string
    {
        return $this->getFromJson('metadata', 'establishment');
    }

    /**
     * Mutator for the restaurant's establishment type (restaurant, café, bakery...).
     *
     * @param $establishment string|null
     */
    public function setEstablishmentAttribute(?string $establishment): void
    {
        $this->setToJson('metadata', 'establishment', $establishment);
    }

    /**
     * Accessor for the restaurant's locale.
     *
     * @return string|null
     */
    public function getLocaleAttribute(): ?string
    {
        return $this->getFromJson('metadata', 'locale');
    }

    /**
     * Mutator for the restaurant's locale.
     *
     * @param $locale string|null
     */
    public function setLocaleAttribute(?string $locale): void
    {
        $this->setToJson('metadata', 'locale', $locale);
    }

    /**
     * Accessor for the restaurant's phone.
     *
     * @return string|null
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->getFromJson('metadata', 'phone');
    }

    /**
     * Mutator for the restaurant's phone.
     *
     * @param $phone string|null
     */
    public function setPhoneAttribute(?string $phone): void
    {
        $this->setToJson('metadata', 'phone', $phone);
    }

    /**
     * Accessor for the restaurant's email.
     *
     * @return string|null
     */
    public function getEmailAttribute(): ?string
    {
        return $this->getFromJson('metadata', 'email');
    }

    /**
     * Mutator for the restaurant's email.
     *
     * @param $email string|null
     */
    public function setEmailAttribute(?string $email): void
    {
        $this->setToJson('metadata', 'email', $email);
    }

    /**
     * Accessor for the restaurant's location link.
     *
     * @return string|null
     */
    public function getLocationAttribute(): ?string
    {
        return $this->getFromJson('metadata', 'location');
    }

    /**
     * Mutator for the restaurant's location link.
     *
     * @param $location string|null
     */
    public function setLocationAttribute(?string $location): void
    {
        $this->setToJson('metadata', 'location', $location);
    }

    /**
     * Accessor for the restaurant's website link.
     *
     * @return string|null
     */
    public function getWebsiteAttribute(): ?string
    {
        return $this->getFromJson('metadata', 'website');
    }

    /**
     * Mutator for the restaurant's website link.
     *
     * @param $website string|null
     */
    public function setWebsiteAttribute(?string $website): void
    {
        $this->setToJson('metadata', 'website', $website);
    }

    /**
     * Accessor for the restaurant's notes.
     *
     * @return string[]|null
     */
    public function getNotesAttribute(): ?array
    {
        return $this->getFromJson('metadata', 'notes');
    }

    /**
     * Mutator for the restaurant's notes.
     *
     * @param $notes string[]|null
     */
    public function setNotesAttribute(array $notes): void
    {
        $this->setToJson('metadata', 'notes', $notes);
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

    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return $this->id;
    }
}
