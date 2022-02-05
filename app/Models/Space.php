<?php

namespace App\Models;

use App\Models\Interfaces\CategorizableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\CategorizableTrait;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\SpaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Space.
 *
 * @property string $title
 * @property string|null $description
 * @property float $price
 * @property int $floor
 * @property int $number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static SpaceFactory factory(...$parameters)
 */
class Space extends BaseModel implements
    SoftDeletableInterface,
    CategorizableInterface,
    LoggableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use CategorizableTrait;
    use LoggableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'description',
        'number',
        'floor',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
    ];

    /**
     * Array of column names changes of which should be logged.
     *
     * @var array
     */
    protected array $logFields = [
        'price',
    ];
}
