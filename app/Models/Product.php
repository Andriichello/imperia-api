<?php

namespace App\Models;

use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\CategorizableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Interfaces\TaggableInterface;
use App\Models\Morphs\Category;
use App\Models\Morphs\Tag;
use App\Models\Morphs\Taggable;
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
    ArchivableInterface,
    LoggableInterface,
    MediableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use TaggableTrait;
    use CategorizableTrait;
    use ArchivableTrait;
    use LoggableTrait;
    use MediableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
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
     * @param DatabaseBuilder $query
     *
     * @return ProductQueryBuilder
     */
    public function newEloquentBuilder($query): ProductQueryBuilder
    {
        return new ProductQueryBuilder($query);
    }
}
