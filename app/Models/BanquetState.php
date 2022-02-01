<?php

namespace App\Models;

use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class BanquetState.
 *
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Banquet[]|Collection $banquets
 */
class BanquetState extends BaseModel implements SoftDeletableInterface
{
    use SoftDeletableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'description',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'banquets',
    ];

    /**
     * Banquets associated with the model.
     *
     * @return HasMany
     */
    public function banquets(): HasMany
    {
        return $this->hasMany(Banquet::class, 'state_id', 'id');
    }
}
