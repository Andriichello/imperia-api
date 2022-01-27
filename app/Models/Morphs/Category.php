<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class Category.
 *
 * @property string $title
 * @property string|null $description
 * @property float|null $amount
 * @property float|null $percent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Category extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'morph_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'title',
        'description',
    ];
}
