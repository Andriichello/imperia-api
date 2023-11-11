<?php

namespace App\Repositories;

use App\Models\Banquet;
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
        if (empty($attributes['title'])) {
            $wasTitleEmpty = true;
            $attributes['title'] = __('columns.banquet');
        }

        /** @var Banquet $model */
        $model = parent::create($attributes);

        if ($wasTitleEmpty ?? false) {
            $model->title .= '-' . $model->id;
            $model->save();
        }

        $this->createComments($model, $attributes);

        $model->order()->firstOrCreate();

        return $model;
    }

    public function update(Model $model, array $attributes): bool
    {
        /** @var Banquet $model */
        if (array_key_exists('title', $attributes) && empty($attributes['title'])) {
            $attributes['title'] = __('columns.banquet') . '-' . $model->id;
        }

        $result = parent::update($model, $attributes);
        $this->updateComments($model, $attributes);

        return $result;
    }
}
