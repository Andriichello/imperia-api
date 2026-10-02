<?php

namespace App\Repositories\Editor;

use App\Models\Restaurant;
use App\Models\RestaurantNote;
use App\Models\Schedule;
use App\Models\ScheduleException;
use App\Models\Scopes\ArchivedScope;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class RestaurantEditorRepository.
 *
 * Changes of the restaurant itself: its details, notes, photos and hours.
 */
class RestaurantEditorRepository extends EditorRepository
{
    /**
     * Load everything of the restaurant the editor shows: its notes, photos, hours and
     * menus with their categories and dishes (hidden and archived ones included).
     *
     * @param Restaurant $restaurant
     *
     * @return Restaurant
     */
    public function load(Restaurant $restaurant): Restaurant
    {
        $withArchived = fn (Relation $query) => $query->withoutGlobalScope(ArchivedScope::class);

        return $restaurant->load([
            'notes',
            'media',
            'schedules',
            'scheduleExceptions',
            'dishMenus' => fn (Relation $query) => $query->withoutGlobalScope(ArchivedScope::class)
                ->orderByDesc('popularity')
                ->orderBy('id'),
            'dishMenus.categories' => $withArchived,
            'dishMenus.categories.dishes' => $withArchived,
            'dishMenus.categories.dishes.variants',
            'dishMenus.categories.dishes.media',
        ]);
    }

    /**
     * Update details of the restaurant (name, type, contacts) and its brand colors.
     *
     * @param Restaurant $restaurant
     * @param array $data
     *
     * @return Restaurant
     */
    public function update(Restaurant $restaurant, array $data): Restaurant
    {
        $this->translate($restaurant, $data, ['name', 'address']);

        $restaurant->fill(Arr::only($data, ['establishment', 'phone', 'brand_primary', 'brand_primary_content']));
        $restaurant->save();

        return $restaurant;
    }

    /**
     * Replace notes of the restaurant with the given ones, in their order:
     * notes, which are left out, are deleted, ones without an id are added.
     *
     * @param Restaurant $restaurant
     * @param array $notes
     *
     * @return void
     */
    public function replaceNotes(Restaurant $restaurant, array $notes): void
    {
        DB::transaction(function () use ($restaurant, $notes) {
            $kept = array_filter(Arr::pluck($notes, 'id'));

            /** @var RestaurantNote $note */
            foreach ($restaurant->notes()->whereNotIn('id', $kept)->get() as $note) {
                $note->delete();
            }

            foreach (array_values($notes) as $index => $data) {
                /** @var RestaurantNote $note */
                $note = empty($data['id'])
                    ? new RestaurantNote(['restaurant_id' => $restaurant->id])
                    : $restaurant->notes()->findOrFail($data['id']);

                $note->putTranslations('text', $data['text']);
                $note->is_hidden = (bool) ($data['is_hidden'] ?? false);
                // numbered from 1, like the admin's sortable lists do
                $note->order = $index + 1;
                $note->save();
            }
        });
    }

    /**
     * Set photos of the restaurant, in their order (the first one is the cover).
     *
     * @param Restaurant $restaurant
     * @param int[] $ids
     *
     * @return void
     */
    public function setPhotos(Restaurant $restaurant, array $ids): void
    {
        $restaurant->setMedia(...$ids);

        $this->forgetWebsite($restaurant->id);
    }

    /**
     * Replace time zone, weekly hours, special days and the temporary closure of the restaurant.
     *
     * @param Restaurant $restaurant
     * @param array $data
     *
     * @return void
     */
    public function updateHours(Restaurant $restaurant, array $data): void
    {
        DB::transaction(function () use ($restaurant, $data) {
            // the weekly hours are replaced: closed days have none
            /** @var Schedule $schedule */
            foreach ($restaurant->schedules()->get() as $schedule) {
                $schedule->delete();
            }

            foreach ($data['weekdays'] as $weekday => $intervals) {
                foreach ($intervals as $interval) {
                    $restaurant->schedules()->create([
                        ...Arr::only($interval, ['beg_hour', 'beg_minute', 'end_hour', 'end_minute']),
                        'weekday' => $weekday,
                        'archived' => false,
                    ]);
                }
            }

            $kept = array_filter(Arr::pluck($data['exceptions'], 'id'));

            /** @var ScheduleException $exception */
            foreach ($restaurant->scheduleExceptions()->whereNotIn('id', $kept)->get() as $exception) {
                $exception->delete();
            }

            foreach ($data['exceptions'] as $values) {
                /** @var ScheduleException $exception */
                $exception = empty($values['id'])
                    ? new ScheduleException(['restaurant_id' => $restaurant->id])
                    : $restaurant->scheduleExceptions()->findOrFail($values['id']);

                $isClosed = (bool) $values['is_closed'];

                $exception->fill([
                    'starts_on' => $values['starts_on'],
                    'ends_on' => $values['ends_on'] ?? $values['starts_on'],
                    'is_closed' => $isClosed,
                    'beg_hour' => $isClosed ? null : $values['beg_hour'],
                    'beg_minute' => $isClosed ? null : $values['beg_minute'],
                    'end_hour' => $isClosed ? null : $values['end_hour'],
                    'end_minute' => $isClosed ? null : $values['end_minute'],
                ]);
                $exception->putTranslations('reason', $values['reason'] ?? null);
                $exception->save();
            }

            $restaurant->timezone = $data['timezone'];
            $restaurant->closed_until = $data['closed_until'] ?? null;
            $restaurant->putTranslations('closed_reason', $data['closed_reason'] ?? null);
            $restaurant->save();
        });
    }
}
