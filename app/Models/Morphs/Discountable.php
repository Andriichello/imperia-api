<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Database\Factories\Morphs\DiscountableFactory;
use Database\Factories\Morphs\DiscountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 *
 * @method static DiscountableFactory factory(...$parameters)
 */
class Discountable extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
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
