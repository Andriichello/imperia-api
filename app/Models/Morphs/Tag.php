<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Restaurant;
use App\Queries\TagQueryBuilder;
use Carbon\Carbon;
use Database\Factories\Morphs\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;

/**
 * Class Tag.
 *
 * @property int|null $restaurant_id
 * @property string|null $target
 * @property string $title
 * @property string|null $description
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Taggable[]|Collection $taggables
 * @property Restaurant|null $restaurant
 *
 * @method static TagQueryBuilder query()
 * @method static TagFactory factory(...$parameters)
 */
class Tag extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'restaurant_id',
        'target',
        'title',
        'description',
        'metadata',
    ];

    /**
     * List of relationship names.
     *
     * @var array
     */
    protected array $relationships = [
        'taggables',
        'restaurant',
    ];

    /**
     * Related taggables.
     *
     * @return HasMany
     */
    public function taggables(): HasMany
    {
        return $this->hasMany(Taggable::class, 'tag_id', 'id');
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
     * @return TagQueryBuilder
     */
    public function newEloquentBuilder($query): TagQueryBuilder
    {
        return new TagQueryBuilder($query);
    }
}
