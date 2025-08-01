<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use Carbon\Carbon;
use Database\Factories\Morphs\TaggableFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Taggable.
 *
 * @property int $tag_id
 * @property int $taggable_id
 * @property string $taggable_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Tag $tag
 * @property BaseModel $tagged
 *
 * @method static TaggableFactory factory(...$parameters)
 */
class Taggable extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tag_id',
        'taggable_id',
        'taggable_type',
    ];

    /**
     * The taggable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'tag',
        'tagged',
    ];

    /**
     * Related tag.
     *
     * @return BelongsTo
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    /**
     * Related tagged model.
     *
     * @return MorphTo
     */
    public function tagged(): MorphTo
    {
        /** @var Builder|MorphTo $morphTo */
        $morphTo = $this->morphTo('tagged', 'taggable_type', 'taggable_id', 'id');
        $morphTo->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);

        return $morphTo;
    }

    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return data_get($this->tag, 'restaurant_id');
    }
}
