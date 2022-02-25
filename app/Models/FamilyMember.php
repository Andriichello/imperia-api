<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\FamilyMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class FamilyMember.
 *
 * @property int $relative_id
 * @property string $name
 * @property string $relation
 * @property Carbon $birthdate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Customer $relative
 *
 * @method static FamilyMemberFactory factory(...$parameters)
 */
class FamilyMember extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
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
}
