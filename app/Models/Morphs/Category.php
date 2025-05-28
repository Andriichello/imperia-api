<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\TaggableInterface;
use App\Models\Restaurant;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\TaggableTrait;
use App\Queries\CategoryQueryBuilder;
use Carbon\Carbon;
use Database\Factories\Morphs\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;

/**
 * Class Category.
 *
 * @property int|null $restaurant_id
 * @property string $slug
 * @property string|null $target
 * @property string $title
 * @property string|null $description
 * @property bool|null $archived
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Categorizable[]|Collection $categorizables
 * @property Restaurant|null $restaurant
 *
 * @method static CategoryQueryBuilder query()
 * @method static CategoryFactory factory(...$parameters)
 */
class Category extends BaseModel implements
    MediableInterface,
    TaggableInterface
{
    use ArchivableTrait;
    use HasFactory;
    use MediableTrait;
    use TaggableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
        'slug',
        'target',
        'title',
        'description',
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
        'categorizables',
        'restaurant',
        'tags',
    ];

    /**
     * Related categorizables.
     *
     * @return HasMany
     */
    public function categorizables(): HasMany
    {
        return $this->hasMany(Categorizable::class, 'category_id', 'id');
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
     * Target property mutator.
     *
     * @param string|null $target
     *
     * @return void
     */
    public function setTargetAttribute(?string $target): void
    {
        $this->attributes['target'] = $target ? slugClass($target) : null;
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return CategoryQueryBuilder
     */
    public function newEloquentBuilder($query): CategoryQueryBuilder
    {
        return new CategoryQueryBuilder($query);
    }
}
