<?php

namespace App\Models;

use App\Helpers\ContentLocale;
use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\HideableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SchedulableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\HideableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SchedulableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Models\Traits\TranslatableTrait;
use App\Queries\DishCategoryQueryBuilder;
use Carbon\Carbon;
use Database\Factories\DishCategoryFactory;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class DishCategory.
 *
 * @property int $menu_id
 * @property string $slug
 * @property string $title
 * @property string|null $description
 * @property bool|null $archived
 * @property bool $is_hidden
 * @property Carbon|null $archived_at
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property DishMenu $menu
 * @property Dish[]|Collection $dishes
 *
 * @method static DishCategoryQueryBuilder query()
 * @method static DishCategoryFactory factory(...$parameters)
 */
class DishCategory extends BaseModel implements
    ArchivableInterface,
    HideableInterface,
    MediableInterface,
    SchedulableInterface,
    SoftDeletableInterface,
    TranslatableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use ArchivableTrait;
    use HideableTrait;
    use MediableTrait;
    use SchedulableTrait;
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
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'menu',
        'restaurant',
    ];

    /**
     * Menu associated with the model.
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
     * Get the dishes of the category, in their order.
     *
     * @return HasMany
     */
    public function dishes(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(Dish::class, 'category_id')
            ->orderByDesc('popularity')
            ->orderBy('id');
    }

    /**
     * Get all dishes of the category, including archived and hidden ones.
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
     * Default language of the category's content (its restaurant's one).
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
     * @return DishCategoryQueryBuilder
     */
    public function newEloquentBuilder($query): DishCategoryQueryBuilder
    {
        return new DishCategoryQueryBuilder($query);
    }
}
