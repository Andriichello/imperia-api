<?php

namespace App\Models\Orders;

use App\Constrainters\Implementations\AmountConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Models\BaseModel;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductOrderField extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_order_fields';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'amount',
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
            'product_id' => IdentifierConstrainter::getRules(true),
            'amount' => AmountConstrainter::getRules(true),
        ];
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'product',
    ];

    /**
     * Get product associated with the model.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get order associated with the model.
     */
    public function order()
    {
        return $this->belongsTo(TicketOrder::class, 'order_id', 'id');
    }
}
