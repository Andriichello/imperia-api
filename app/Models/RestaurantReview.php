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
 * @property int $id
 * @property int $restaurant_id
 * @property string|null $ip
 * @property string $reviewer
 * @property int $score
 * @property string|null $title
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Restaurant $restaurant
 *
 * @method static RestaurantReviewQueryBuilder query()
 * @method static RestaurantReviewFactory factory(...$parameters)
 */
class RestaurantReview extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'ip',
        'reviewer',
        'score',
        'title',
        'description',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'restaurant',
    ];

    /**
     * Holidays associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
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
