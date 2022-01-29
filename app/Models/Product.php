<?php

namespace App\Models;

use App\Models\Traits\CategorizableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Product.
 *
 * @property string $title
 * @property string|null $description
 * @property float|null $price
 * @property float|null $weight
 * @property int|null $menu_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Menu|null $menu
 *
 * @method static ProductFactory factory(...$parameters)
 */
class Product extends BaseModel
{
    use HasFactory;
    use SoftDeletableTrait;
    use CategorizableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'weight',
        'menu_id',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'menu',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'weight' => 'float',
    ];

    /**
     * Get the menu associated with the model.
     *
     * @return BelongsTo
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }
}
