<?php

namespace App\Models;

use App\Models\Traits\SoftDeletableTrait;
use App\Queries\RestaurantQueryBuilder;
use Carbon\Carbon;
use Database\Factories\RestaurantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Restaurant.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $country
 * @property string $city
 * @property string $place
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static RestaurantQueryBuilder query()
 * @method static RestaurantFactory factory(...$parameters)
 */
class Restaurant extends BaseModel
{
    use HasFactory;
    use SoftDeletableTrait;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'metadata' => '{}',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'slug',
        'name',
        'country',
        'city',
        'place',
    ];

    /**
     * Banquets associated with the model.
     *
     * @return HasMany
     */
    public function banquets(): HasMany
    {
        return $this->hasMany(Banquet::class, 'restaurant_id', 'id');
    }
}
