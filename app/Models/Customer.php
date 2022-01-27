<?php

namespace App\Models;

use App\Models\Traits\SoftDeletable;
use Carbon\Carbon;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Customer.
 *
 * @property string $name
 * @property string $surname
 * @property string $phone
 * @property string|null $email
 * @property Carbon|null $birthdate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property FamilyMember[]|Collection $familyMembers
 *
 * @method static CustomerFactory factory(...$parameters)
 */
class Customer extends BaseModel
{
    use HasFactory;
    use SoftDeletable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'surname',
        'phone',
        'email',
        'birthdate',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'familyMembers',
    ];

    /**
     * Related family members.
     *
     * @return HasMany
     */
    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'relative_id', 'id');
    }
}
