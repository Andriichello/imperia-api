<?php

namespace Tests\Models\Stubs;

use App\Models\BaseModel;

/**
 * Class BaseStub.
 *
 * @property string $name
 * @property string $metadata
 */
class BaseStub extends BaseModel
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
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'metadata',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'name' => 'Stub',
        'metadata' => '{}',
    ];
}
