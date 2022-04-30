<?php

namespace App\Models;

use App\Models\Interfaces\JsonFieldInterface;
use App\Models\Traits\JsonFieldTrait;
use App\Traits\StaticMethodsAccess;
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
class BaseModel extends Model implements JsonFieldInterface
{
    use JsonFieldTrait;
    use StaticMethodsAccess;

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
    ];

    /**
     * Accessor for the model type string.
     *
     * @return string
     */
    public function getTypeAttribute(): string
    {
        return slugClass(static::class);
    }
}
