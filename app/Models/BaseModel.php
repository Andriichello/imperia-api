<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel.
 *
 * @property int $id
 * @property string $type
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
     * @return string|null
     */
    public function getTypeAttribute(): ?string
    {
        return $this->table ?? null;
    }
}
