<?php

namespace App\Models;

use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Menu.
 *
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Product[]|Collection $products
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
}
