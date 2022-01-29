<?php

namespace Tests\Models;

use App\Models\BaseModel;

/**
 * Class StubModel.
 *
 * @property string $name
 * @property string $metadata
 */
class StubModel extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stubs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'metadata',
    ];
}
