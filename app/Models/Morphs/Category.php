<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Traits\MediableTrait;
use App\Queries\CategoryQueryBuilder;
use Carbon\Carbon;
use Database\Factories\Morphs\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;

/**
 * Class Category.
 *
 * @property string $slug
 * @property string|null $target
 * @property string $title
 * @property string|null $metadata
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Categorizable[]|Collection $categorizables
 *
 * @method static CategoryQueryBuilder query()
 * @method static CategoryFactory factory(...$parameters)
 */
class Category extends BaseModel
{
    use HasFactory;
    use MediableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'slug',
        'target',
        'title',
        'description',
        'metadata',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'categorizables',
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
