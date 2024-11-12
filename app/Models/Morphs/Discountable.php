<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use Carbon\Carbon;
use Database\Factories\Morphs\DiscountableFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property Discount $discount
 * @property BaseModel $discounted
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
     * List of relationship names.
     *
     * @var array
     */
    protected array $relationships = [
        'discount',
        'discounted',
    ];

    /**
     * Related discount.
     *
     * @return BelongsTo
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Related discounted model.
     *
     * @return MorphTo
     */
    public function discounted(): MorphTo
    {
        /** @var Builder|MorphTo $morphTo */
        $morphTo = $this->morphTo('discounted', 'discountable_type', 'discountable_id', 'id');
        $morphTo->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);

        return $morphTo;
    }
}
