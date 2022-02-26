<?php

namespace App\Models;

use App\Models\Interfaces\CategorizableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\CategorizableTrait;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\MediableTrait;
use App\Models\Traits\SoftDeletableTrait;
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
 * @property bool $archived
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static ServiceFactory factory(...$parameters)
 */
class Service extends BaseModel implements
    SoftDeletableInterface,
    CategorizableInterface,
    LoggableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use CategorizableTrait;
    use LoggableTrait;
    use MediableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'description',
        'once_paid_price',
        'hourly_paid_price',
        'archived',
        'metadata',
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

    /**
     * Array of column names changes of which should be logged.
     *
     * @var array
     */
    protected array $logFields = [
        'once_paid_price',
        'hourly_paid_price',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'categories',
    ];
}
