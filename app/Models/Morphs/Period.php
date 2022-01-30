<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class Period.
 *
 * @property string|null $title
 * @property Carbon|null $start_at
 * @property Carbon|null $end_at
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Period extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'start_at',
        'end_at',
        'metadata',
    ];
}
