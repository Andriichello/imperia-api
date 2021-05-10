<?php

namespace App\Models;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\CommentTextConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text',
        'target_id',
        'target_type',
        'container_id',
        'container_type',
    ];

    /**
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @var bool $inOtherObject
     * @return array
     */
    public static function getValidationRules($forInsert = false, $inOtherObject = false) {
        return [
            'text' => CommentTextConstrainter::getRules(true),
            'target_id' => IdentifierConstrainter::getRules(true),
            'target_type' => NameConstrainter::getRules(true),
            'container_id' => IdentifierConstrainter::getRules($forInsert && !$inOtherObject),
            'container_type' => NameConstrainter::getRules($forInsert && !$inOtherObject),
        ];
    }

    /**
     * Get the target model.
     */
    public function target()
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id', 'id');
    }

    /**
     * Get the container model.
     */
    public function container()
    {
        return $this->morphTo(__FUNCTION__, 'container_type', 'container_id', 'id');
    }
}
