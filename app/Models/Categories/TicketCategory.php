<?php

namespace App\Models\Categories;

use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Models\BaseDeletableModel;
use App\Models\BaseModel;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketCategory extends BaseDeletableModel
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
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @return array
     */
    public static function getValidationRules($forInsert = false) {
        return Category::getValidationRules($forInsert, 'ticket');
    }

    /**
     * Get tickets associated with the model.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'category_id', 'id');
    }
}
