<?php

namespace App\Models\Orders;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\AmountConstrainter;
use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Constrainters\Implementations\PriceConstrainter;
use App\Models\Banquet;
use App\Models\BaseDeletableModel;
use App\Models\BaseModel;
use App\Models\Orders\Order;
use App\Models\Comment;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductOrder extends BaseDeletableModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'banquet_id',
        'discount_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['fields'];

    protected $cascadeDeletes = ['fields'];

    /**
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @return array
     */
    public static function getValidationRules($forInsert = false) {
        $rules = Order::getValidationRules($forInsert, 'product');
        $rules['items'] = Constrainter::getRules(false);
        $rules['items.*.id'] = IdentifierConstrainter::getRules($forInsert);
        $rules['items.*.amount'] = AmountConstrainter::getRules($forInsert);

        return $rules;
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public $appends = ['items', 'comments', 'total'];

    /**
     * Get product items associated with the model.
     *
     * @return  array
     */
    public function getItemsAttribute()
    {
        $comments = Comment::select()
            ->where('container_id', '=', $this->banquet_id)
            ->where('container_type', '=', 'banquets')
            ->where('target_type', '=', 'products')
            ->get();

        $fields = $this->fields;
        $items = [];
        foreach ($fields as $field) {
            $vars = $field->product->toArray();
            $vars['amount'] = $field->amount;
            $vars['comments'] = [];

            foreach ($comments as $comment) {
                if ($comment->target_id == $vars['id']) {
                    $vars['comments'][] = $comment;
                }
            }
            $items[] = $vars;
        }

        return $items;
    }

    /**
     * Get comments associated with the model.
     *
     * @return  array
     */
    public function getCommentsAttribute() {
        return Comment::select()
            ->where('container_id', '=', $this->banquet_id)
            ->where('container_type', '=', 'banquets')
            ->where('target_id', '=', $this->id)
            ->where('target_type', '=', $this->table)
            ->get();
    }

    /**
     * Get total price of all items within the model.
     *
     * @return float
     */
    public function getTotalAttribute() {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item['price'] * $item['amount'];
        }

        return $total;
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'discount',
        'fields',
    ];

    /**
     * Get discount associated with the model.
     */
    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id', 'id');
    }

    /**
     * Get fields associated with the model.
     */
    public function fields()
    {
        return $this->hasMany(ProductOrderField::class, 'order_id', 'id');
    }

    /**
     * Get banquet associated with the model.
     */
    public function banquet()
    {
        return $this->belongsTo(Banquet::class, 'banquet_id', 'id');
    }
}
