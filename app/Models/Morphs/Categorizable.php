<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Categorizable.
 *
 * @property int $category_id
 * @property int $categorizable_id
 * @property string $categorizable_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Model|null $categorizable
 */
class Categorizable extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'categorizable_id',
        'categorizable_type',
    ];

    /**
     * The categorizable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'categorizable',
    ];

    /**
     * Get the target model.
     *
     * @return MorphTo
     */
    public function categorizable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'categorizable_type', 'categorizable_id', 'id');
    }
}
