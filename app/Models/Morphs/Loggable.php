<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Loggable.
 *
 * @property int $log_id
 * @property int $loggable_id
 * @property string $loggable_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Model|null $loggable
 */
class Loggable extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'log_id',
        'loggable_id',
        'loggable_type',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'loggable',
    ];

    /**
     * Get the target model.
     *
     * @return MorphTo
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'loggable_type', 'loggable_id', 'id');
    }
}
