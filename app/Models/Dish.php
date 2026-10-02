<?php

namespace App\Models;

use App\Helpers\ContentLocale;
use App\Models\Interfaces\AlterableInterface;
use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\FlaggableInterface;
use App\Models\Interfaces\HideableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Traits\AlterableTrait;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\FlaggableTrait;
use App\Models\Traits\HideableTrait;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Models\Traits\TranslatableTrait;
use App\Queries\DishQueryBuilder;
use Carbon\Carbon;
use Database\Factories\DishFactory;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Dish.
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
    AlterableInterface,
    TranslatableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use ArchivableTrait;
    use LoggableTrait;
    use MediableTrait;
    use FlaggableTrait;
    use AlterableTrait;
    use HideableTrait;
    use TranslatableTrait;

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
