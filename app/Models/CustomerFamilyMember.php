<?php

namespace App\Models;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use Symfony\Component\Validator\Constraints as Assert;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerFamilyMember extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_family_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'birthdate',
        'customer_id',
    ];

    /**
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @return array
     */
    public static function getValidationRules($forInsert = false) {
        return [
            'name' => NameConstrainter::getRules(false),
            'customer_id' => IdentifierConstrainter::getRules(false),
            'birthdate' => Constrainter::getRules(false, [new Assert\Date()]),
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
