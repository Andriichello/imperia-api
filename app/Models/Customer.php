<?php

namespace App\Models;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\EmailConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Constrainters\Implementations\PhoneConstrainter;
use Symfony\Component\Validator\Constraints as Assert;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'surname',
        'phone',
        'email',
        'birthdate',
    ];

    /**
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @return array
     */
    public static function getValidationRules($forInsert = false) {
        return [
            'name' => NameConstrainter::getRules($forInsert),
            'surname' => NameConstrainter::getRules($forInsert),
            'phone' => PhoneConstrainter::getRules($forInsert),
            'email' => EmailConstrainter::getRules(false),
            'birthdate' => Constrainter::getRules(false, [new Assert\Date()]),
        ];
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'familyMembers',
    ];

    public function familyMembers()
    {
        return $this->hasMany(CustomerFamilyMember::class, 'customer_id', 'id');
    }
}
