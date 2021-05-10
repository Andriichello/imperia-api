<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['targetComments', 'containerComments'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public $appends = ['type'];

    /**
     * Accessor for the table type.
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        return $this->table;
    }

    /**
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @return array
     */
    public static function getValidationRules($forInsert = false) {
        return [];
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    /**
     * Get all comments for model as a container.
     */
    public function containerComments()
    {
        return $this->morphMany(Comment::class, 'container', 'container_type', 'container_id', 'id');
    }

    /**
     * Get all comments for model as a target.
     */
    public function targetComments()
    {
        return $this->morphMany(Comment::class, 'target', 'target_type', 'target_id', 'id');
    }
}
