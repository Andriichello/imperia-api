<?php

namespace App\Repositories;

use App\Enums\Weekday;
use App\Models\Restaurant;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
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

    public function update(Restaurant|Model $model, array $attributes): bool
    {
        return DB::transaction(function () use ($model, $attributes) {
            /** @var Restaurant $model */
            if (!parent::update($model, $attributes)) {
                return false;
            }

            if (array_key_exists('schedules', $attributes)) {
                $schedules = $attributes['schedules'];

                $model->schedules
                    ->each(function (Schedule $schedule) use ($schedules) {
                        $params = Arr::first(
                            $schedules,
                            fn(array $params) => $schedule->weekday === $params['weekday']
                        );

                        $schedule->fill($params);
                        $schedule->saveOrFail();
                    });

                $weekdays = array_map(
                    fn (Schedule $schedule) => $schedule->weekday,
                    $model->schedules->all()
                );

                $weekdaysToCreate = array_diff(Weekday::getValues(), $weekdays);

                foreach ($weekdaysToCreate as $weekday) {
                    $params = Arr::first(
                        $schedules,
                        fn(array $params) => $weekday === $params['weekday']
                    );

                    if (empty($params['restaurant_id'])) {
                        $params['restaurant_id'] = $model->id;
                    }

                    $schedule = new Schedule($params);
                    $schedule->saveOrFail();
                }

                $weekdays = array_map(
                    fn ($schedule) => $schedule['weekday'],
                    $schedules
                );

                $weekdaysToDelete = array_diff(Weekday::getValues(), $weekdays);

                if (!empty($weekdaysToDelete)) {
                    /** @phpstan-ignore-next-line  */
                    $model->schedules()
                        ->whereNotIn('weekday', $weekdays)
                        ->delete();
                }
            }

            return true;
        });
    }
}
