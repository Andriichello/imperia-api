<?php

namespace App\Models\Categories;

use App\Models\BaseModel;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketCategory extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get tickets associated with the model.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'category_id', 'id');
    }
}
