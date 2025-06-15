<?php

namespace App\Models;

use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
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
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Restaurant $restaurant
 * @property Dish[]|Collection $dishes
 * @property DishCategory[]|Collection $categories
 */
class DishMenu extends BaseModel implements
    ArchivableInterface,
    SoftDeletableInterface,
    MediableInterface
{
    use SoftDeletableTrait;
    use ArchivableTrait;
    use MediableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dish_menus';

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
        'popularity',
        'metadata',
    ];

    /**
     * Array of relation names that should be deleted with the current model.
     *
     * @var array
     */
    protected array $cascadeDeletes = [
        'dishes',
        'categories',
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
        return $this->hasMany(DishCategory::class, 'menu_id');
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
}
