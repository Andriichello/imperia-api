<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Discountable.
 *
 * @property int $discount_id
 * @property int $discountable_id
 * @property string $discountable_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Model|null $discountable
 */
class Discountable extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'discount_id',
        'discountable_id',
        'discountable_type',
    ];

    /**
     * The discountable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'discountable',
    ];

    /**
     * Get the target model.
     *
     * @return MorphTo
     */
    public function discountable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'discountable_type', 'discountable_id', 'id');
    }
}
