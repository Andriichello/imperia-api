<?php

namespace App\Models;

use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Space.
 *
 * @property string $title
 * @property string|null $description
 * @property float price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static TicketFactory factory(...$parameters)
 */
class Ticket extends BaseModel
{
    use HasFactory;
    use SoftDeletableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
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
}
