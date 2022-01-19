<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImperiaUser extends BaseDeletableModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'password',
        'role_id',
        'api_token',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'role',
    ];

    /**
     * Get the role associated with the user.
     */
    public function role()
    {
        return $this->belongsTo(ImperiaRole::class, 'role_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($user) {
            $user->api_token = hash('sha256', uniqid());
        });
    }
}
