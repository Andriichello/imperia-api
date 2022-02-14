<?php

namespace App\Models;

use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Morphs\Category;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * Class Menu.
 *
 * @property string $title
 * @property string|null $description
 * @property bool $archived
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Product[]|Collection $products
 * @property Category[]|Collection $categories
 *
 * @method static MenuFactory factory(...$parameters)
 */
class Menu extends BaseModel implements
    SoftDeletableInterface
{
    use HasFactory;
    use SoftDeletableTrait;

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
        'categories',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'products',
    ];

    /**
     * Get the products associated with the model.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'menu_id', 'id');
    }

    /**
     * Relation for categories associated with products in menu.
     *
     * @return EloquentBuilder|Builder
     */
    public function categories(): EloquentBuilder|Builder
    {
        $slug = slugClass(Product::class);
        return Category::query()
            ->where('target', $slug)
            ->join('categorizables', 'categorizables.category_id', '=', 'categories.id')
            ->where('categorizables.categorizable_type', $slug)
            ->join('products', 'products.id', '=', 'categorizables.categorizable_id')
            ->where('products.menu_id', $this->id)
            ->select('categories.*')
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
}
