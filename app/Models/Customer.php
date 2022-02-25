<?php

namespace App\Models;

use App\Models\Interfaces\CommentableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\SoftDeletableTrait;
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
class Customer extends BaseModel implements
    SoftDeletableInterface,
    CommentableInterface,
    LoggableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use CommentableTrait;
    use LoggableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'surname',
        'phone',
        'email',
        'birthdate',
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
        'familyMembers',
    ];

    /**
     * Array of column names changes of which should be logged.
     *
     * @var array
     */
    protected array $logFields = [
        'email',
        'phone',
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
