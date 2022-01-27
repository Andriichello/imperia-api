<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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
 * @property Model|null $periodical
 */
class Periodical extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
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
        'periodical',
    ];

    /**
     * Get the related model.
     *
     * @return MorphTo
     */
    public function periodical(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'periodical_type', 'periodical_id', 'id');
    }
}
