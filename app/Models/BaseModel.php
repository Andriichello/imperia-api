<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasFactory;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public $appends = ['type'];

    public function getTypeAttribute()
    {
        return $this->table;
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
