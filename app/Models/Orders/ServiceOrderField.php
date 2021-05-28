<?php

namespace App\Models\Orders;

use App\Constrainters\Implementations\AmountConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Models\BaseDeletableModel;
use App\Models\BaseModel;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceOrderField extends BaseDeletableModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_order_fields';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'service_id',
        'amount',
        'duration',
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
            'service_id' => IdentifierConstrainter::getRules(true),
            'amount' => AmountConstrainter::getRules($forInsert),
            'duration' => AmountConstrainter::getRules($forInsert),
        ];
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'service',
    ];

    /**
     * Get service associated with the model.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    /**
     * Get order associated with the model.
     */
    public function order()
    {
        return $this->belongsTo(TicketOrder::class, 'order_id', 'id');
    }
}
