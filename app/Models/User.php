<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\JsonFieldTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\UserQueryBuilder;
use App\Traits\StaticMethodsAccess;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
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
 * @property int|null $restaurant_id
 * @property int|null $customer_id
 * @property string $type
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $email_verified_at
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property array<int> $restaurants
 *
 * @property Restaurant|null $restaurant
 * @property Customer|null $customer
 * @property Banquet[]|Collection $banquets
 * @property Notification[]|Collection $inbounds
 * @property Notification[]|Collection $outbounds
 *
 * @method static UserQueryBuilder query()
 * @method static UserFactory factory(...$parameters)
 */
class User extends Authenticatable implements SoftDeletableInterface, FilamentUser
{
    use StaticMethodsAccess;
    use SoftDeletableTrait;
    use HasApiTokens;
    use Notifiable;
    use HasFactory;
    use HasRoles;
    use JsonFieldTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
        'name',
        'email',
        'password',
        'metadata',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
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
        'restaurant',
        'customer',
        'banquets',
        'inbounds',
        'outbounds',
    ];

    /**
     * Restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Customer associated with the model.
     *
     * @return HasOne
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }

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
     * Notifications, which user have received or should receive.
     *
     * @return HasMany
     */
    public function inbounds(): HasMany
    {
        return $this->hasMany(Notification::class, 'receiver_id', 'id');
    }

    /**
     * Notifications, which user have sent or scheduled to be sent.
     *
     * @return HasMany
     */
    public function outbounds(): HasMany
    {
        return $this->hasMany(Notification::class, 'sender_id', 'id');
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
     * Accessor for the related customer id.
     *
     * @return int|null
     */
    public function getCustomerIdAttribute(): ?int
    {
        return $this->customer?->id;
    }

    /**
     * If true then user should only be granted preview rights.
     *
     * @return bool
     */
    public function isPreviewOnly(): bool
    {
        return (bool) $this->getFromJson('metadata', 'isPreviewOnly', true);
    }

    /**
     * Get array of ids for restaurants, which current access
     * (applies only to staff members and admins).
     *
     * @return array<int>
     */
    public function getRestaurantsAttribute(): array
    {
        return $this->getFromJson('metadata', 'restaurants', []);
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

    /**
     * Determine if user is a customer.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    /**
     * Determine if user is a customer.
     *
     * @return bool
     */
    public function isManager(): bool
    {
        return $this->hasRole(UserRole::Manager);
    }

    /**
     * Determine if user is a staff member.
     *
     * @return bool
     */
    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    /**
     * Determine if user is a customer.
     *
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->hasRole(UserRole::Customer)
            /** @phpstan-ignore-next-line */
            && $this->customer()->exists();
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return UserQueryBuilder
     */
    public function newEloquentBuilder($query): UserQueryBuilder
    {
        return new UserQueryBuilder($query);
    }

    /**
     * @param Panel $panel
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isStaff();
    }

    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return $this->restaurant_id;
    }
}
