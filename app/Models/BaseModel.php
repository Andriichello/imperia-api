<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel.
 *
 * @property int $id
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BaseModel extends Model
{
    use BaseModelTrait;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public $appends = [
        'type',
    ];

    /**
     * Accessor for the model type string.
     *
     * @return string
     */
    public function getTypeAttribute(): string
    {
        return static::class;
    }
}
