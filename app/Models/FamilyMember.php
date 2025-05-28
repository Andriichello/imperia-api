<?php

namespace App\Models;

use App\Queries\FamilyMemberQueryBuilder;
use Carbon\Carbon;
use Database\Factories\FamilyMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class FamilyMember.
 *
 * @property int $relative_id
 * @property string $name
 * @property string|null $relation
 * @property Carbon $birthdate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Customer $relative
 *
 * @method static FamilyMemberQueryBuilder query()
 * @method static FamilyMemberFactory factory(...$parameters)
 */
class FamilyMember extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'relation',
        'birthdate',
        'relative_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'birthdate' => 'datetime',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'relative',
    ];

    /**
     * Customer to whom the model is related.
     *
     * @return BelongsTo
     */
    public function relative(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'relative_id', 'id');
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return FamilyMemberQueryBuilder
     */
    public function newEloquentBuilder($query): FamilyMemberQueryBuilder
    {
        return new FamilyMemberQueryBuilder($query);
    }


    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return data_get($this->relative, 'restaurant_id');
    }
}
