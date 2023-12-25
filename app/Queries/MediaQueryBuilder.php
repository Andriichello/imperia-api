<?php

namespace App\Queries;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;

/**
 * Class MediaQueryBuilder.
 */
class MediaQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User|null $user
     *
     * @return static
     */
    public function index(?User $user = null): static
    {
        $query = parent::index($user);

        if ($user->restaurant_id) {
            $query->withRestaurant($user->restaurant_id);
        }

        return $query;
    }

    /**
     * Select media with given names.
     *
     * @param string ...$names
     *
     * @return static
     */
    public function name(string ...$names): static
    {
        $this->whereIn('name', $names);

        return $this;
    }

    /**
     * Select media with given disks.
     *
     * @param string ...$disks
     *
     * @return static
     */
    public function disk(string ...$disks): static
    {
        $this->whereIn('disk', $disks);

        return $this;
    }

    /**
     * Select media from given folders.
     *
     * @param string ...$folders
     *
     * @return static
     */
    public function folder(string ...$folders): static
    {
        $this->whereIn('folder', $folders);

        return $this;
    }

    /**
     * Select media attached to model with given id and type.
     *
     * @param mixed $id
     * @param string $type
     *
     * @return static
     */
    public function attachedBy(mixed $id, string $type): static
    {
        $joinClause = function (JoinClause $join) use ($id, $type) {
            $join->on('mediables.media_id', '=', 'media.id')
                ->where('mediables.mediable_type', $type)
                ->where('mediables.mediable_id', $id);
        };

        $this->join('mediables', $joinClause);

        return $this;
    }

    /**
     * Select media attached to given model.
     *
     * @param BaseModel $model
     *
     * @return static
     */
    public function attachedTo(BaseModel $model): static
    {
        return $this->attachedBy($model->id, $model->type);
    }

    /**
     * Only media for given restaurants.
     *
     * @param mixed ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(mixed ...$restaurants): static
    {
        $ids = $this->extract('id', ...$restaurants);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }
}
