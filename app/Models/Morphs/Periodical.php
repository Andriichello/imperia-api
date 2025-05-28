<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use Carbon\Carbon;
use Database\Factories\Morphs\PeriodicalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Periodical.
 *
 * @property int $period_id
 * @property int $periodical_id
 * @property string $periodical_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Period $period
 * @property BaseModel $periodic
 *
 * @method static PeriodicalFactory factory(...$parameters)
 */
class Periodical extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'period_id',
        'periodical_id',
        'periodical_type',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'period',
        'periodic',
    ];

    /**
     * Related period.
     *
     * @return BelongsTo
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Related periodic model.
     *
     * @return MorphTo
     */
    public function periodic(): MorphTo
    {
        /** @var Builder|MorphTo $morphTo */
        $morphTo = $this->morphTo('periodic', 'periodical_type', 'periodical_id', 'id');
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
        return data_get($this->periodic, 'restaurant_id');
    }
}
