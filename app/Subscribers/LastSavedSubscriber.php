<?php

namespace App\Subscribers;

use App\Models\BaseModel;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Restaurant;
use App\Models\RestaurantNote;
use App\Models\Schedule;
use App\Models\ScheduleException;

/**
 * Class LastSavedSubscriber.
 *
 * Remembers when a restaurant's page (its details, hours, notes, menus, ...) was last saved,
 * and by whom ("Last saved today at 14:32 by Anna" on the admin's dashboard). Changes, which
 * fire no events (orders, photos), are marked by their repositories.
 */
class LastSavedSubscriber extends BaseSubscriber
{
    /**
     * Models shown on the restaurant's page.
     *
     * @var string[]
     */
    protected array $models = [
        Restaurant::class,
        RestaurantNote::class,
        Schedule::class,
        ScheduleException::class,
        DishMenu::class,
        DishCategory::class,
        Dish::class,
        DishVariant::class,
    ];

    /**
     * Map events to the methods.
     *
     * @return void
     */
    protected function map(): void
    {
        $map = [];

        foreach ($this->models as $model) {
            $map[$model::eloquentEvent('saved')] = 'saved';
            $map[$model::eloquentEvent('deleted')] = 'saved';
            $map[$model::eloquentEvent('restored')] = 'saved';
        }

        $this->map = $map;
    }

    /**
     * Mark the model's restaurant as saved.
     *
     * @param BaseModel $model
     *
     * @return void
     */
    public function saved(BaseModel $model): void
    {
        Restaurant::markSaved($model->getRestaurantId());
    }
}
