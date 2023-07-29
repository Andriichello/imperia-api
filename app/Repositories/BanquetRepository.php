<?php

namespace App\Repositories;

use App\Models\Banquet;
use App\Models\Orders\Order;
use App\Repositories\Traits\CommentableRepositoryTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BanquetRepository.
 */
class BanquetRepository extends CrudRepository
{
    use CommentableRepositoryTrait;

    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Banquet::class;

    public function create(array $attributes): Model
    {
        /** @var Banquet $model */
        $model = parent::create($attributes);
        $this->createComments($model, $attributes);

        $model->order()->firstOrCreate();

        return $model;
    }

    public function update(Model $model, array $attributes): bool
    {
        /** @var Banquet $model */
        $result = parent::update($model, $attributes);
        $this->updateComments($model, $attributes);

        return $result;
    }
}
