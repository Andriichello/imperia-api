<?php

namespace App\Models;

use App\Queries\RestaurantReviewQueryBuilder;
use Carbon\Carbon;
use Database\Factories\RestaurantReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class RestaurantReview.
 *
 * A guest's review of a restaurant: a rating from 1 to 5, with a name and a text, if they gave
 * them, in the language they wrote it in. It's public once the restaurant approves it; rejected
 * ones (spam, insults, personal details) never are. Neither the guest's IP nor their device's
 * token is stored as it is, only their hashes (see `RestaurantReview::hash()`).
 *
 * @property int $id
 * @property int $restaurant_id
 * @property int $rating
 * @property string|null $name
 * @property string|null $text
 * @property string|null $locale
 * @property string $status
 * @property Carbon|null $moderated_at
 * @property int|null $moderated_by
 * @property string|null $ip_hash
 * @property string|null $client_hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Restaurant $restaurant
 * @property User|null $moderator
 *
 * @method static RestaurantReviewQueryBuilder query()
 * @method static RestaurantReviewFactory factory(...$parameters)
 */
class RestaurantReview extends BaseModel
{
    use HasFactory;

    /**
     * Status of a review, which waits for the restaurant to check it.
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Status of a public review.
     */
    public const STATUS_APPROVED = 'approved';

    /**
     * Status of a review, which is never public (spam, insults, personal details).
     */
    public const STATUS_REJECTED = 'rejected';

    /**
     * Statuses of reviews.
     *
     * @var string[]
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
        'rating',
        'name',
        'text',
        'locale',
        'status',
        'moderated_at',
        'moderated_by',
        'ip_hash',
        'client_hash',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'ip_hash',
        'client_hash',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'rating' => 'integer',
        'moderated_at' => 'datetime',
    ];

    /**
     * The restaurant, which the review is about.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    /**
     * The user, who approved or rejected the review.
     *
     * @return BelongsTo
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * A hash of a guest's IP or device token, which stands for it (it can't be turned back).
     *
     * @param string $value
     *
     * @return string
     */
    public static function hash(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return RestaurantReviewQueryBuilder
     */
    public function newEloquentBuilder($query): RestaurantReviewQueryBuilder
    {
        return new RestaurantReviewQueryBuilder($query);
    }
}
