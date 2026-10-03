<?php

namespace App\Models;

use App\Helpers\ContentLocale;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SchedulableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\Morphs\Category;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SchedulableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Models\Traits\TranslatableTrait;
use App\Queries\RestaurantQueryBuilder;
use Carbon\Carbon;
use Database\Factories\RestaurantFactory;
use DateTimeZone;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class Restaurant.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $country
 * @property string $city
 * @property string $place
 * @property string|null $address
 * @property string $timezone
 * @property Carbon|null $closed_until
 * @property string|null $closed_reason
 * @property Carbon|null $last_saved_at
 * @property int|null $last_saved_by
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
 * @property string|null $brand_primary
 * @property string|null $brand_primary_content
 *
 * @property Menu[]|Collection $menus
 * @property Product[]|Collection $products
 * @property Category[]|Collection $categories
 * @property Schedule[]|Collection $schedules
 * @property ScheduleException[]|Collection $scheduleExceptions
 * @property RestaurantNote[]|Collection $notes
 * @property Holiday[]|Collection $holidays
 * @property Holiday[]|Collection $relevantHolidays
 * @property RestaurantReview[]|Collection $reviews
 *
 * @property DishMenu[]|Collection $dishMenus
 * @property DishCategory[]|Collection $dishCategories
 * @property Dish[]|Collection $dishes
 * @property MenuVersion[]|Collection $versions
 * @property User|null $lastSavedBy
 *
 * @method static RestaurantQueryBuilder query()
 * @method static RestaurantFactory factory(...$parameters)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Restaurant extends BaseModel implements
    MediableInterface,
    SchedulableInterface,
    SoftDeletableInterface,
    TranslatableInterface
{
    use HasFactory;
    use MediableTrait;
    use SchedulableTrait;
    use SoftDeletableTrait;
    use TranslatableTrait;

    /**
     * The model's attributes.
     *
     * @var array<string, mixed>
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
        'timezone',
        'popularity',
        'phone',
        'email',
        'website',
        'location',
        'full_address',
        'timezone_offset',
        'locale',
        'currency',
        'establishment',
        'address',
        'closed_until',
        'closed_reason',
        'brand_primary',
        'brand_primary_content',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
        'phone',
        'email',
        'website',
        'location',
        'full_address',
        'timezone_offset',
        'locale',
        'currency',
        'establishment',
        'brand_primary',
        'brand_primary_content',
    ];

    /**
     * The attributes that have translations.
     *
     * @var string[]
     */
    protected array $translatable = [
        'name',
        'address',
        'closed_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'closed_until' => 'date',
        'last_saved_at' => 'datetime',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'menus',
        'products',
        'categories',
        'schedules',
        'holidays',
        'relevantHolidays',
        'reviews',
        'dishMenus',
        'dishCategories',
        'dishes',
        'notes',
        'scheduleExceptions',
    ];

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
     * Get the special days of the restaurant (holidays and short days), from the earliest.
     *
     * @return HasMany
     */
    public function scheduleExceptions(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(ScheduleException::class)
            ->orderBy('starts_on')
            ->orderBy('id');
    }

    /**
     * Get the notes of the restaurant, in their order (hidden ones included).
     *
     * @return HasMany
     */
    public function notes(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(RestaurantNote::class)
            ->orderBy('order')
            ->orderBy('id');
    }

    /**
     * Scheduled versions of the restaurant's page.
     *
     * @return HasMany
     */
    public function versions(): HasMany
    {
        return $this->hasMany(MenuVersion::class);
    }

    /**
     * Remember, that pages of the restaurants were saved now (by the signed-in user, if any).
     * The restaurants' own `updated_at` stays as it is.
     *
     * @param int|null ...$ids
     *
     * @return void
     */
    public static function markSaved(?int ...$ids): void
    {
        $ids = array_unique(array_filter($ids));

        if (empty($ids)) {
            return;
        }

        DB::table('restaurants')
            ->whereIn('id', $ids)
            ->update([
                'last_saved_at' => Carbon::now(),
                'last_saved_by' => Auth::id(),
            ]);
    }

    /**
     * The user, who last saved the restaurant's page.
     *
     * @return BelongsTo
     */
    public function lastSavedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_saved_by');
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
     * Accessor for the restaurant's full address: its address,
     * or (if it has none) its place, city and country.
     *
     * @return Attribute
     */
    public function fullAddress(): Attribute
    {
        return Attribute::get(
            function () {
                // the address written in the editor, in the current language
                if (!empty($this->address)) {
                    return $this->address;
                }

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
     * Accessor for the restaurant's brand color (a hex one, e.g. `#3bb517`),
     * used for buttons, tabs and tinted backgrounds of its pages.
     *
     * @return string|null
     */
    public function getBrandPrimaryAttribute(): ?string
    {
        return $this->getFromJson('metadata', 'brand_primary');
    }

    /**
     * Mutator for the restaurant's brand color.
     *
     * @param string|null $color
     */
    public function setBrandPrimaryAttribute(?string $color): void
    {
        $this->setToJson('metadata', 'brand_primary', $color ? strtolower($color) : null);
    }

    /**
     * Accessor for the color of text and icons on the restaurant's brand color tints.
     *
     * @return string|null
     */
    public function getBrandPrimaryContentAttribute(): ?string
    {
        return $this->getFromJson('metadata', 'brand_primary_content');
    }

    /**
     * Mutator for the color of text and icons on the restaurant's brand color tints.
     *
     * @param string|null $color
     */
    public function setBrandPrimaryContentAttribute(?string $color): void
    {
        $this->setToJson('metadata', 'brand_primary_content', $color ? strtolower($color) : null);
    }

    /**
     * Default language of the restaurant's content.
     *
     * @return string
     */
    public function getDefaultLocale(): string
    {
        return ContentLocale::fromMetadata($this->getAttributes()['metadata'] ?? null);
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
