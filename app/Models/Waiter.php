<?php

namespace App\Models;

use App\Models\Interfaces\CommentableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Interfaces\TippableInterface;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Models\Traits\TippableTrait;
use App\Queries\WaiterQueryBuilder;
use Carbon\Carbon;
use Database\Factories\WaiterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class Waiter.
 *
 * @property int|null $user_id
 * @property int|null $restaurant_id
 * @property string $name
 * @property string $surname
 * @property string $fullName
 * @property string|null $phone
 * @property string|null $email
 * @property Carbon|null $birthdate
 * @property string|null $about
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Restaurant|null $restaurant
 *
 * @method static WaiterQueryBuilder query()
 * @method static WaiterFactory factory(...$parameters)
 */
class Waiter extends BaseModel implements
    SoftDeletableInterface,
    CommentableInterface,
    LoggableInterface,
    MediableInterface,
    TippableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use CommentableTrait;
    use LoggableTrait;
    use MediableTrait;
    use TippableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'restaurant_id',
        'name',
        'surname',
        'phone',
        'email',
        'birthdate',
        'about',
        'metadata',
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
        'media',
        'restaurant',
        'tips',
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
     * Related restaurant.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id', 'id');
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
     * @return WaiterQueryBuilder
     */
    public function newEloquentBuilder($query): WaiterQueryBuilder
    {
        return new WaiterQueryBuilder($query);
    }
}
