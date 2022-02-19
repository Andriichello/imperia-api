<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Database\Factories\Morphs\PeriodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Period.
 *
 * @property string|null $title
 * @property Carbon|null $start_at
 * @property Carbon|null $end_at
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Period[]|Collection $periodicals
 *
 * @method static PeriodFactory factory(...$parameters)
 */
class Period extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'start_at',
        'end_at',
        'metadata',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'periodicals',
    ];

    /**
     * Related periodicals.
     *
     * @return HasMany
     */
    public function periodicals(): HasMany
    {
        return $this->hasMany(Periodical::class, 'period_id', 'id');
    }
}
