<?php

namespace App\Models;

use App\Queries\RestaurantQueryBuilder;
use Database\Factories\RestaurantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 *
 * @method static RestaurantQueryBuilder query()
 * @method static RestaurantFactory factory(...$parameters)
 */
class Restaurant extends BaseModel
{
    use HasFactory;

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
}
