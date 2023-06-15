<?php

namespace App\Models;

use App\Http\Filters\RestaurantsFilter;
use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Morphs\Category;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\MenuQueryBuilder;
use Carbon\Carbon;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Menu.
 *
 * @property string $title
 * @property string|null $description
 * @property bool $archived
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Product[]|Collection $products
 * @property Category[]|Collection $categories
 * @property Restaurant[]|Collection $restaurants
 *
 * @method static MenuQueryBuilder query()
 * @method static MenuFactory factory(...$parameters)
 */
class Menu extends BaseModel implements
    ArchivableInterface,
    SoftDeletableInterface,
    MediableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use ArchivableTrait;
    use MediableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'description',
        'archived',
        'popularity',
        'metadata',
    ];

    /**
     * Array of relation names that should be deleted with the current model.
     *
     * @var array
     */
    protected array $cascadeDeletes = [
        'products',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
        'categories',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'media',
        'products',
        'restaurants',
    ];

    /**
     * Get the products associated with the model.
     *
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'menu_product');
    }

    /**
     * Get the restaurants associated with the model.
     *
     * @return BelongsToMany
     */
    public function restaurants(): BelongsToMany
    {
        return $this->belongsToMany(Restaurant::class, 'restaurant_menu');
    }

    /**
     * Relation for categories associated with products in menu.
     *
     * @return EloquentBuilder|Builder
     */
    public function categories(): EloquentBuilder|Builder
    {
        $slug = slugClass(Product::class);

        $query = Category::query()
            ->where('target', $slug)
            ->join('categorizables', 'categorizables.category_id', '=', 'categories.id')
            ->where('categorizables.categorizable_type', $slug)
            ->join('products', 'products.id', '=', 'categorizables.categorizable_id')
            ->join('menu_product', 'menu_product.product_id', '=', 'products.id')
            ->where('menu_product.menu_id', $this->id)
            ->orderByDesc('popularity');

        if (request('filter.restaurants')) {
            $filter = new RestaurantsFilter('restaurant_category', 'category_id');
            // @phpstan-ignore-next-line
            $filter($query, request('filter.restaurants'), 'filter.restaurants');
        }

        return $query->select('categories.*')
            ->distinct();
    }

    /**
     * Categories associated with products in menu.
     *
     * @return Collection
     */
    public function getCategoriesAttribute(): Collection
    {
        if (!isset($this->attributes['categories'])) {
            $this->attributes['categories'] = $this->categories()->get();
        }
        return $this->attributes['categories'];
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return MenuQueryBuilder
     */
    public function newEloquentBuilder($query): MenuQueryBuilder
    {
        return new MenuQueryBuilder($query);
    }
}
