<?php

namespace App\Models;

use App\Models\Traits\SoftDeletable;
use Carbon\Carbon;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Service.
 *
 * @property string $title
 * @property string|null $description
 * @property float $once_paid_price
 * @property float $hourly_paid_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static ServiceFactory factory(...$parameters)
 */
class Service extends BaseModel
{
    use HasFactory;
    use SoftDeletable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'once_paid_price',
        'hourly_paid_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'once_paid_price' => 'float',
        'hourly_paid_price' => 'float',
    ];
}
