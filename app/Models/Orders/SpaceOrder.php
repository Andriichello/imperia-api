<?php

namespace App\Models\Orders;

use App\Models\Banquet;
use App\Models\BaseModel;
use App\Models\Comment;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpaceOrder extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'space_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'banquet_id',
        'discount_id',
        'items',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['fields'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public $appends = ['items', 'comments'];

    /**
     * Get space items associated with the model.
     *
     * @return  array
     */
    public function getItemsAttribute()
    {
        $comments = Comment::select()
            ->where('container_id', '=', $this->banquet_id)
            ->where('container_type', '=', 'banquets')
            ->where('target_type', '=', 'spaces')
            ->get();

        $fields = $this->fields;
        $items = [];
        foreach ($fields as $field) {
            $vars = $field->space->toArray();
            $vars['beg_datetime'] = $field->beg_datetime;
            $vars['end_datetime'] = $field->end_datetime;
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
        return $this->hasMany(SpaceOrderField::class, 'order_id', 'id');
    }

    /**
     * Get banquet associated with the model.
     */
    public function banquet()
    {
        return $this->belongsTo(Banquet::class, 'banquet_id', 'id');
    }
}
