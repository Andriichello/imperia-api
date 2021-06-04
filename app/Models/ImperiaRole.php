<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImperiaRole extends BaseDeletableModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'imperia_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'can_read',
        'can_insert',
        'can_modify',
        'is_owner',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'can_read' => 'boolean',
        'can_insert' => 'boolean',
        'can_modify' => 'boolean',
        'is_owner' => 'boolean',
    ];

    /**
     * Get users associated with the role.
     */
    public function users()
    {
        return $this->hasMany(ImperiaUser::class, 'role_id', 'id');
    }
}
