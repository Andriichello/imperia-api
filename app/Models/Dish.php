<?php

namespace App\Models;

use App\Helpers\ContentLocale;
use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\FlaggableInterface;
use App\Models\Interfaces\HideableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SchedulableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\FlaggableTrait;
use App\Models\Traits\HideableTrait;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SchedulableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Models\Traits\TranslatableTrait;
use App\Queries\DishQueryBuilder;
use Carbon\Carbon;
use Database\Factories\DishFactory;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class Dish.
 *
 * Its sizes are its variants, at least one of them is shown to guests. The dish's own size
 * columns (price, weight, ...) mirror its first size: the cheapest one shown to guests.
 *
 * @property int $menu_id
 * @property int|null $category_id
 * @property string|null $slug
 * @property string $title
 * @property string|null $description
 * @property string|null $badge
 * @property float|null $price
 * @property string|null $weight
 * @property string|null $weight_unit
 * @property integer|null $calories
 * @property integer|null $preparation_time
 * @property bool $archived
 * @property bool $is_hidden
 * @property Carbon|null $archived_at
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property DishMenu $menu
 * @property DishCategory|null $category
 * @property DishVariant[]|Collection $variants
 * @property DishVariant[]|Collection $sizes
 *
 * @method static DishQueryBuilder query()
 * @method static DishFactory factory(...$parameters)
 */
class Dish extends BaseModel implements
    SoftDeletableInterface,
    ArchivableInterface,
    HideableInterface,
    LoggableInterface,
    MediableInterface,
    FlaggableInterface,
    SchedulableInterface,
    TranslatableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use ArchivableTrait;
    use LoggableTrait;
    use MediableTrait;
    use FlaggableTrait;
    use SchedulableTrait;
    use HideableTrait;
    use TranslatableTrait;

    /**
     * Attributes of a size, which the dish's own columns mirror.
     *
     * @var string[]
     */
    public const SIZE = ['price', 'weight', 'weight_unit', 'calories', 'preparation_time'];

    /**
     * Whether new dishes get their first size from their own size columns.
     *
     * @var bool
     */
    protected static bool $createsFirstSize = true;

    /**
     * Whether the dish's own size columns are being set from its first size.
     *
     * @var bool
     */
    protected bool $mirroring = false;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'archived' => false,
        'is_hidden' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'menu_id',
        'category_id',
        'slug',
        'title',
        'description',
        'badge',
        'price',
        'weight',
        'weight_unit',
        'archived',
        'is_hidden',
        'archived_at',
        'popularity',
        'metadata',
        'calories',
        'preparation_time',
        'flags',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
        'flags',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'menu',
        'category',
        'media',
        'variants',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'is_hidden' => 'boolean',
        'archived_at' => 'datetime',
    ];

    /**
     * The attributes that have translations.
     *
     * @var string[]
     */
    protected array $translatable = [
        'title',
        'description',
        'badge',
    ];

    /**
     * Array of column names changes of which should be logged.
     *
     * @var array
     */
    protected array $logFields = [
        'price',
    ];

    /**
     * Get the menu associated with the model.
     *
     * @return BelongsTo
     */
    public function menu(): BelongsTo
    {
        // @phpstan-ignore-next-line
        return $this->belongsTo(DishMenu::class, 'menu_id')
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Get the category associated with the model.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        // @phpstan-ignore-next-line
        return $this->belongsTo(DishCategory::class, 'category_id')
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Get the variants associated with the model.
     * Deleted variants are never included, not even when deleted dishes are requested.
     *
     * @return HasMany
     */
    public function variants(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(DishVariant::class, 'dish_id')
            ->orderBy('price')
            ->withoutGlobalScopes([SoftDeletableScope::class])
            ->whereNull('dish_variants.deleted_at');
    }

    /**
     * Get the sizes of the dish, hidden and archived ones included, from the cheapest.
     *
     * @return HasMany
     */
    public function sizes(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(DishVariant::class, 'dish_id')
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class])
            ->whereNull('dish_variants.deleted_at')
            ->orderBy('price')
            ->orderBy('id');
    }

    /**
     * Get all variants associated with the model, including archived and deleted ones.
     * Used by the admin panel, which filters them itself.
     *
     * @return HasMany
     */
    public function allVariants(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(DishVariant::class, 'dish_id')
            ->orderBy('price')
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Create the first size of a new dish from its own size columns, and change the first size,
     * when they're changed (e.g. by the API or the admin panel).
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::created(function (Dish $dish) {
            if (static::$createsFirstSize && !$dish->sizes()->exists()) {
                $dish->sizes()->create(Arr::only($dish->getAttributes(), static::SIZE));
            }
        });

        static::updated(function (Dish $dish) {
            if ($dish->mirroring || !$dish->wasChanged(static::SIZE)) {
                return;
            }

            $dish->firstSize()
                ?->fill(Arr::only($dish->getAttributes(), static::SIZE))
                ->save();
        });
    }

    /**
     * Create dishes without their first size (e.g. copies, whose sizes are copied too).
     *
     * @param callable $callback
     *
     * @return mixed
     */
    public static function withoutFirstSize(callable $callback): mixed
    {
        $creates = static::$createsFirstSize;
        static::$createsFirstSize = false;

        try {
            return $callback();
        } finally {
            static::$createsFirstSize = $creates;
        }
    }

    /**
     * The first size of the dish: the cheapest one shown to guests.
     *
     * @return DishVariant|null
     */
    public function firstSize(): ?DishVariant
    {
        /** @var DishVariant|null $size */
        $size = $this->sizes()
            ->where('dish_variants.archived', false)
            ->where('dish_variants.is_hidden', false)
            ->first();

        return $size;
    }

    /**
     * Set the dish's own size columns to its first size.
     *
     * @return void
     */
    public function mirrorFirstSize(): void
    {
        $first = $this->firstSize();

        if (!$first) {
            return;
        }

        $this->fill(Arr::only($first->getAttributes(), static::SIZE));

        if (!$this->isDirty()) {
            return;
        }

        $this->mirroring = true;

        try {
            $this->save();
        } finally {
            $this->mirroring = false;
        }
    }

    /**
     * Default language of the dish's content (its restaurant's one).
     *
     * @return string
     */
    public function getDefaultLocale(): string
    {
        return ContentLocale::instance()->ofMenu($this->menu_id);
    }

    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return $this->menu->getRestaurantId();
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return DishQueryBuilder
     */
    public function newEloquentBuilder($query): DishQueryBuilder
    {
        return new DishQueryBuilder($query);
    }
}
