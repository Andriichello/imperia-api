<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommentController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Model's primary keys.
     *
     * @var string[]
     */
    protected $primaryKeys = ['id', 'container_id', 'container_type', 'target_id', 'target_type'];

    /**
     * Create new Model instance and store it in the database.
     *
     * @param array $columns
     * @return Model
     */
    public function createModel(array $columns): Model
    {
        $instance = parent::createModel($columns);

        $id = DB::table($instance->getTable())
            ->where($this->extract($instance->toArray(), $this->primaryKeys('id')))
            ->max('id');

        if (isset($instance) && isset($id)) {
            $instance->id = $id;
        }
        return $instance;
    }
}
