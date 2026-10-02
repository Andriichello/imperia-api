<?php

namespace App\Models;

use App\Models\Interfaces\AlterableInterface;
use App\Helpers\ContentLocale;
use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\HideableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\Scopes\ArchivedScope;
use App\Models\Traits\AlterableTrait;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\HideableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Models\Traits\TranslatableTrait;
use App\Queries\DishMenuQueryBuilder;
use Carbon\Carbon;
use Database\Factories\DishMenuFactory;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class DishMenu.
 *
 * @property int $restaurant_id
 * @property string|null $slug
 * @property string $title
 * @property string|null $description
 * @property bool $archived
 * @property bool $is_hidden
 * @property Carbon|null $archived_at
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Restaurant $restaurant
 * @property Dish[]|Collection $dishes
 * @property DishCategory[]|Collection $categories
 *
 * @method static DishMenuQueryBuilder query()
 * @method static DishMenuFactory factory(...$parameters)
 */
class DishMenu extends BaseModel implements
    ArchivableInterface,
    HideableInterface,
    SoftDeletableInterface,
    MediableInterface,
    AlterableInterface,
    TranslatableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use ArchivableTrait;
    use HideableTrait;
    use MediableTrait;
    use AlterableTrait;
    use TranslatableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dish_menus';

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
        'restaurant_id',
        'slug',
        'title',
        'description',
        'archived',
        'is_hidden',
        'archived_at',
        'popularity',
        'metadata',
    ];

    /**
     * The attributes that have translations.
     *
     * @var string[]
     */
    protected array $translatable = [
        'title',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_hidden' => 'boolean',
        'archived_at' => 'datetime',
    ];

    /**
     * Array of relation names that should be deleted with the current model.
     *
     * @var array
     */
    protected array $cascadeDeletes = [
        'allDishes',
        'allCategories',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'media',
        'dishes',
        'categories',
        'restaurant',
    ];

    /**
     * Get the dishes associated with the model.
     *
     * @return HasMany
     */
    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class, 'menu_id');
    }

    /**
     * Get the categories associated with the model.
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(DishCategory::class, 'menu_id')
            ->orderByDesc('popularity')
            ->orderBy('id');
    }

    /**
     * Get all dishes associated with the model, including archived ones.
     * Used for cascading deletes and restores.
     *
     * @return HasMany
     */
    public function allDishes(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->dishes()
            ->withoutGlobalScope(ArchivedScope::class);
    }

    /**
     * Get all categories associated with the model, including archived ones.
     * Used for cascading deletes and restores.
     *
     * @return HasMany
     */
    public function allCategories(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->categories()
            ->withoutGlobalScope(ArchivedScope::class);
    }

    /**
     * Get the restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Default language of the menu's content (its restaurant's one).
     *
     * @return string
     */
    public function getDefaultLocale(): string
    {
        return ContentLocale::instance()->ofRestaurant($this->restaurant_id);
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return DishMenuQueryBuilder
     */
    public function newEloquentBuilder($query): DishMenuQueryBuilder
    {
        return new DishMenuQueryBuilder($query);
    }
}
