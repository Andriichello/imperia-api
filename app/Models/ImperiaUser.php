<?php

namespace App\Models;

use App\Constrainters\Implementations\ApiTokenConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Constrainters\Implementations\PasswordConstrainter;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ImperiaUser extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'imperia_users';

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
     * Get array of model's validation rules.
     *
     * @return array
     * @var bool $forInsert
     */
    public static function getValidationRules($forInsert = false)
    {
        $additionalNameRules = $forInsert ? ['unique:users'] : [];
        $rules = [
            'name' => NameConstrainter::getRules($forInsert, $additionalNameRules),
            'password' => PasswordConstrainter::getRules($forInsert),
            'role_id' => IdentifierConstrainter::getRules($forInsert),
        ];

        if (!$forInsert) {
            $rules['api_token'] = ApiTokenConstrainter::getRules(true);
        }
        return $rules;
    }

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
}
