<?php

namespace App\Models\Orders;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Models\BaseModel;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Symfony\Component\Validator\Constraints as Assert;

class SpaceOrderField extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'space_order_fields';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'space_id',
        'beg_datetime',
        'end_datetime',
    ];

    /**
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @return array
     */
    public static function getValidationRules($forInsert = false) {
        return [
            'order_id' => IdentifierConstrainter::getRules(true),
            'space_id' => IdentifierConstrainter::getRules(true),
            'beg_datetime' => Constrainter::getRules($forInsert, [new Assert\DateTime()]),
            'end_datetime' => Constrainter::getRules($forInsert, [new Assert\DateTime()]),
        ];
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'space',
    ];

    /**
     * Get space associated with the model.
     */
    public function space()
    {
        return $this->belongsTo(Space::class, 'space_id', 'id');
    }

    /**
     * Get order associated with the model.
     */
    public function order()
    {
        return $this->belongsTo(SpaceOrder::class, 'order_id', 'id');
    }
}
