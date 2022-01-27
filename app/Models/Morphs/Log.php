<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class Log.
 *
 * @property string $title
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Log extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'metadata',
    ];
}
