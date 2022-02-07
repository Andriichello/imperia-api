<?php

namespace App\Models;

use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User.
 *
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Banquet[]|Collection $banquets
 *
 * @method static UserFactory factory(...$parameters)
 */
class User extends Authenticatable implements SoftDeletableInterface
{
    use SoftDeletableTrait;
    use HasApiTokens;
    use Notifiable;
    use HasFactory;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'banquets',
    ];

    /**
     * Banquets associated with the model.
     *
     * @return HasMany
     */
    public function banquets(): HasMany
    {
        return $this->hasMany(Banquet::class, 'creator_id', 'id');
    }

    /**
     * Password mutator, which handles encrypting.
     *
     * @param string $password
     *
     * @return void
     */
    public function setPasswordAttribute(string $password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * Accessor for the model type string.
     *
     * @return string
     */
    public function getTypeAttribute(): string
    {
        return slugClass(static::class);
    }

    /**
     * Determines if given password is the same as current one.
     *
     * @param string $password
     *
     * @return bool
     */
    public function isCurrentPassword(string $password): bool
    {
        return Hash::check($password, $this->getOriginal('password'));
    }
}
