<?php

namespace App\Models;

use App\Models\Interfaces\CommentableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\CustomerQueryBuilder;
use Carbon\Carbon;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;

/**
 * Class Customer.
 *
 * @property int|null $user_id
 * @property int|null $restaurant_id
 * @property string $name
 * @property string $surname
 * @property string $fullName
 * @property string|null $phone
 * @property string|null $email
 * @property Carbon|null $birthdate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property User|null $user
 * @property Restaurant|null $restaurant
 * @property Banquet[]|Collection $banquets
 * @property FamilyMember[]|Collection $familyMembers
 *
 * @method static CustomerQueryBuilder query()
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
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
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
        'user',
        'restaurant',
        'banquets',
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
     * Related user.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Related restaurant.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id', 'id');
    }

    /**
     * Related banquets.
     *
     * @return HasMany
     */
    public function banquets(): HasMany
    {
        return $this->hasMany(Banquet::class, 'customer_id', 'id');
    }

    /**
     * Related family members.
     *
     * @return HasMany
     */
    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'relative_id', 'id');
    }

    /**
     * Accessor for the customer's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "$this->name $this->surname";
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return CustomerQueryBuilder
     */
    public function newEloquentBuilder($query): CustomerQueryBuilder
    {
        return new CustomerQueryBuilder($query);
    }
}
