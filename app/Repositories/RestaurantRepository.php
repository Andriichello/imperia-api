<?php

namespace App\Repositories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class RestaurantRepository.
 */
class RestaurantRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Restaurant::class;

    /**
     * Create restaurant from given attributes.
     *
     * @param array $attributes
     *
     * @return Restaurant
     */
    public function create(array $attributes): Model
    {
        /** @var Restaurant $restaurant */
        $restaurant = parent::create($attributes);

        $schedules = data_get($attributes, 'schedules');
        if ($schedules !== null) {
            $this->syncSchedules($schedules, $restaurant);
        }

        return $restaurant;
    }

    /**
     * Update attributes on given restaurant.
     *
     * @param Restaurant $model
     * @param array $attributes
     *
     * @return bool
     */
    public function update(Model $model, array $attributes): bool
    {
        $result = parent::update($model, $attributes);

        $schedules = data_get($attributes, 'schedules');
        if ($schedules !== null) {
            $this->syncSchedules($schedules, $model);
        }

        return $result;
    }

    /**
     * Create or update restaurant with given attributes.
     *
     * @param array $attributes
     *
     * @return Builder|Restaurant
     */
    public function updateOrCreate(array $attributes): Builder|Model
    {
        /** @var Restaurant $restaurant */
        $restaurant = $this->builder()->updateOrCreate($attributes);

        $schedules = data_get($attributes, 'schedules');
        if ($schedules !== null) {
            $this->syncSchedules($schedules, $restaurant);
        }

        return $restaurant;
    }

    /**
     * Sync (automatically create/update/delete) schedules.
     *
     * @param array $schedules
     * @param Restaurant $restaurant
     *
     * @return void
     */
    protected function syncSchedules(array $schedules, Restaurant $restaurant): void
    {
        if (empty($schedules)) {
            $restaurant->schedules()->delete();

            return;
        }

        DB::transaction(function () use ($schedules, $restaurant) {
            foreach ($schedules as $schedule) {
                $schedule['restaurant_id'] = $restaurant->id;

                if (empty($schedule['id'])) {
                    $restaurant->schedules()
                        ->updateOrCreate(['weekday' => $schedule['weekday']], $schedule);

                    continue;
                }

                $restaurant->schedules()
                    ->where('id', $schedule['id'])
                    ->update($schedule);
            }
        });
    }
}
