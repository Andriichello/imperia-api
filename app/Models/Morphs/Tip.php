<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Restaurant;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\TipQueryBuilder;
use Carbon\Carbon;
use Database\Factories\Morphs\TipFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class Tip.
 *
 * @property int|null $restaurant_id
 * @property string $uuid
 * @property float $amount
 * @property float|null $commission
 * @property string|null $note
 * @property int $tippable_id
 * @property string $tippable_type
 * @property Carbon|null $claimed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Restaurant $restaurant
 * @property BaseModel $tippable
 *
 * @method static TipQueryBuilder query()
 * @method static TipFactory factory(...$parameters)
 */
class Tip extends BaseModel implements SoftDeletableInterface
{
    use HasFactory;
    use SoftDeletableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'restaurant_id',
        'uuid',
        'amount',
        'commission',
        'note',
        'tippable_id',
        'tippable_type',
        'claimed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'restaurant',
        'tippable',
    ];

    /**
     * Get the restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id', 'id');
    }

    /**
     * Model, which was tipped.
     *
     * @return MorphTo
     */
    public function tippable(): MorphTo
    {
        /** @var Builder|MorphTo $morphTo */
        $morphTo = $this->morphTo('tippable', 'tippable_type', 'tippable_id', 'id');
        $morphTo->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);

        return $morphTo;
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return TipQueryBuilder
     */
    public function newEloquentBuilder($query): TipQueryBuilder
    {
        return new TipQueryBuilder($query);
    }
}
