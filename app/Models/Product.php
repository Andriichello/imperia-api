<?php

namespace App\Models;

use App\Enums\Hotness;
use App\Models\Interfaces\AlterableInterface;
use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\CategorizableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Interfaces\TaggableInterface;
use App\Models\Traits\AlterableTrait;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\CategorizableTrait;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Models\Traits\TaggableTrait;
use App\Queries\ProductQueryBuilder;
use Carbon\Carbon;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class Product.
 *
 * @property int|null $restaurant_id
 * @property string|null $slug
 * @property string $title
 * @property string|null $description
 * @property float|null $price
 * @property string|null $weight
 * @property string|null $weight_unit
 * @property string|null $badge
 * @property bool $archived
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property int|null $preparation_time
 * @property int|null $calories
 * @property bool|null $is_vegan
 * @property bool|null $is_vegetarian
 * @property bool|null $has_eggs
 * @property bool|null $has_nuts
 * @property Hotness|null $hotness
 *
 * @property Menu[]|Collection $menus
 * @property ProductVariant[]|Collection $variants
 * @property Restaurant|null $restaurant
 *
 * @method static ProductQueryBuilder query()
 * @method static ProductFactory factory(...$parameters)
 */
class Product extends BaseModel implements
    SoftDeletableInterface,
    TaggableInterface,
    CategorizableInterface,
    AlterableInterface,
    ArchivableInterface,
    LoggableInterface,
    MediableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use TaggableTrait;
    use CategorizableTrait;
    use AlterableTrait;
    use ArchivableTrait;
    use LoggableTrait;
    use MediableTrait;

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
        'price',
        'weight',
        'weight_unit',
        'badge',
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
        'menus',
        'media',
        'variants',
        'categories',
        'restaurant',
        'tags',
        'alterations',
        'pendingAlterations',
        'performedAlterations',
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
     * @return BelongsToMany
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_product');
    }

    /**
     * Get the variants associated with the model.
     *
     * @return HasMany
     */
    public function variants(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(ProductVariant::class, 'product_id')
            ->orderBy('price');
    }

    /**
     * Restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Last loaded menu ids.
     *
     * @var array
     */
    public array $menuIds;

    /**
     * Menu ids associated with the model.
     *
     * @param bool $fresh
     *
     * @return int[]
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function menuIds(bool $fresh = false): array
    {
        if (!$fresh && isset($this->menuIds)) {
            return $this->menuIds;
        }

        $this->menuIds = $this->relationLoaded('menus')
            ? $this->menus->pluck('id')->toArray()
            : DB::table('menu_product')
            ->where('product_id', $this->id)
            ->pluck('menu_id')
            ->toArray();

        return $this->menuIds;
    }

    /**
     * Accessor for the `calories` attribute.
     *
     * @return int|null
     */
    public function getCaloriesAttribute(): ?int
    {
        $value = $this->getFromJson('metadata', 'calories');

        return $value === null ? null : (int) $value;
    }

    /**
     * Mutator for the `calories` attribute.
     *
     * @param int|null $calories
     *
     * @return void
     */
    public function setCaloriesAttribute(?int $calories): void
    {
        $this->setToJson('metadata', 'calories', $calories);
    }

    /**
     * Accessor for the `preparation_time` attribute.
     *
     * @return int|null
     */
    public function getPreparationTimeAttribute(): ?int
    {
        $value = $this->getFromJson('metadata', 'preparation_time');

        return $value === null ? null : (int) $value;
    }

    /**
     * Mutator for the `preparation_time` attribute.
     *
     * @param int|null $preparationTime
     *
     * @return void
     */
    public function setPreparationTimeAttribute(?int $preparationTime): void
    {
        $this->setToJson('metadata', 'preparation_time', $preparationTime);
    }

    /**
     * Accessor for the `is_vegan` attribute.
     *
     * @return bool|null
     */
    public function getIsVeganAttribute(): ?bool
    {
        $value = $this->getFromJson('metadata', 'is_vegan');

        return $value === null ? null : (bool) $value;
    }

    /**
     * Mutator for the `is_vegan` attribute.
     *
     * @param bool|null $isVegan
     *
     * @return void
     */
    public function setIsVeganAttribute(?bool $isVegan): void
    {
        $this->setToJson('metadata', 'is_vegan', $isVegan);
    }

    /**
     * Accessor for the `is_vegetarian` attribute.
     *
     * @return bool|null
     */
    public function getIsVegetarianAttribute(): ?bool
    {
        $value = $this->getFromJson('metadata', 'is_vegetarian');

        return $value === null ? null : (bool) $value;
    }

    /**
     * Mutator for the `is_vegetarian` attribute.
     *
     * @param bool|null $isVegetarian
     *
     * @return void
     */
    public function setIsVegetarianAttribute(?bool $isVegetarian): void
    {
        $this->setToJson('metadata', 'is_vegetarian', $isVegetarian);
    }

    /**
     * Accessor for the `has_eggs` attribute.
     *
     * @return bool|null
     */
    public function getHasEggsAttribute(): ?bool
    {
        $value = $this->getFromJson('metadata', 'has_eggs');

        return $value === null ? null : (bool) $value;
    }

    /**
     * Mutator for the `has_eggs` attribute.
     *
     * @param bool|null $hasEggs
     *
     * @return void
     */
    public function setHasEggsAttribute(?bool $hasEggs): void
    {
        $this->setToJson('metadata', 'has_eggs', $hasEggs);
    }

    /**
     * Accessor for the `has_nuts` attribute.
     *
     * @return bool|null
     */
    public function getHasNutsAttribute(): ?bool
    {
        $value = $this->getFromJson('metadata', 'has_nuts');

        return $value === null ? null : (bool) $value;
    }

    /**
     * Mutator for the `has_nuts` attribute.
     *
     * @param bool|null $hasNuts
     *
     * @return void
     */
    public function setHasNutsAttribute(?bool $hasNuts): void
    {
        $this->setToJson('metadata', 'has_nuts', $hasNuts);
    }

    /**
     * Accessor for the `hotness` attribute.
     *
     * @return Hotness|null
     */
    public function getHotnessAttribute(): Hotness|null
    {
        $value = $this->getFromJson('metadata', 'hotness');

        if ($value === null) {
            return null;
        }

        try {
            return Hotness::fromValue($value);
        } catch (Throwable) {
            return Hotness::Unknown();
        }
    }

    /**
     * Mutator for the `hotness` attribute.
     *
     * @param Hotness|string|null $hotness
     *
     * @return void
     */
    public function setHotnessAttribute(Hotness|string|null $hotness): void
    {
        if ($hotness instanceof Hotness) {
            $hotness = $hotness->value;
        }

        $this->setToJson('metadata', 'hotness', $hotness);
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return ProductQueryBuilder
     */
    public function newEloquentBuilder($query): ProductQueryBuilder
    {
        return new ProductQueryBuilder($query);
    }
}
