<?php

namespace App\Models;

use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
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
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property DishMenu $menu
 * @property DishCategory|null $category
 * @property DishVariant[]|Collection $variants
 */
class Dish extends BaseModel implements
    SoftDeletableInterface,
    ArchivableInterface,
    LoggableInterface,
    MediableInterface
{
    use SoftDeletableTrait;
    use ArchivableTrait;
    use LoggableTrait;
    use MediableTrait;

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
        'popularity',
        'metadata',
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
     *
     * @return HasMany
     */
    public function variants(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(DishVariant::class, 'dish_id')
            ->orderBy('price')
            ->withoutGlobalScopes([SoftDeletableScope::class]);
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
}
