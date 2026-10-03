<?php

namespace App\Repositories\Editor;

use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\Morphs\Media;
use App\Models\Restaurant;
use App\Models\RestaurantNote;
use App\Models\RestaurantReview;
use App\Models\Schedule;
use App\Models\ScheduleException;
use App\Models\Scopes\ArchivedScope;
use App\Models\User;
use App\Repositories\MediaRepository;
use App\Repositories\RestaurantReviewRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class RestaurantEditorRepository.
 *
 * Changes of the restaurant itself: its details, notes, photos and hours.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RestaurantEditorRepository extends EditorRepository
{
    /**
     * RestaurantEditorRepository constructor.
     *
     * @param MediaRepository $media
     * @param VersionEditorRepository $versions
     * @param RestaurantReviewRepository $reviews
     */
    public function __construct(
        protected MediaRepository $media,
        protected VersionEditorRepository $versions,
        protected RestaurantReviewRepository $reviews,
    ) {
    }

    /**
     * Restaurants the user can edit (the admin's restaurant switcher).
     *
     * @param User $user
     *
     * @return Collection<int, array>
     */
    public function editableBy(User $user): Collection
    {
        /** @var Collection<int, Restaurant> $restaurants */
        $restaurants = Restaurant::query()
            ->orderBy('id')
            ->get();

        return $restaurants
            ->filter(fn (Restaurant $restaurant) => $user->can('update', $restaurant))
            ->map(fn (Restaurant $restaurant) => [
                'id' => $restaurant->id,
                'slug' => $restaurant->slug,
                'name' => $restaurant->name,
                'default_locale' => $restaurant->getDefaultLocale(),
            ])
            ->values()
            ->toBase();
    }

    /**
     * Load everything of the restaurant the editor shows: its notes, photos, hours,
     * menus with their categories and dishes (hidden and archived ones included, photos too)
     * and the versions, which haven't gone live yet.
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
            'allMedia',
            'schedules',
            'scheduleExceptions',
            'dishMenus' => fn (Relation $query) => $query->withoutGlobalScope(ArchivedScope::class)
                ->orderByDesc('popularity')
                ->orderBy('id'),
            'dishMenus.categories' => $withArchived,
            'dishMenus.categories.dishes' => $withArchived,
            'dishMenus.categories.dishes.sizes',
            'dishMenus.categories.dishes.allMedia',
        ])->setRelation('versions', $this->versions->ofRestaurant($restaurant, true));
    }

    /**
     * What the admin's dashboard shows: what guests see (menus and dishes), when the page was
     * last saved, the hours, upcoming special days, the versions, which haven't gone live yet, and
     * the reviews (the approved ones, and how many wait for approval).
     *
     * @param Restaurant $restaurant
     *
     * @return Restaurant with `menus_count`, `dishes_count`, `reviews_summary` and `pending_reviews`
     */
    public function dashboard(Restaurant $restaurant): Restaurant
    {
        $today = Carbon::now($restaurant->timezone ?: config('app.timezone'))->toDateString();

        $restaurant->load([
            'lastSavedBy',
            'schedules',
            'scheduleExceptions' => fn (Relation $query) => $query->where('ends_on', '>=', $today)
                ->orderBy('starts_on')
                ->limit(20),
        ]);

        $restaurant->setAttribute('menus_count', DishMenu::query()
            ->withoutGlobalScopes()
            ->where('restaurant_id', $restaurant->id)
            ->shownToGuests()
            ->count());

        $restaurant->setAttribute('dishes_count', Dish::query()
            ->withoutGlobalScopes()
            ->withRestaurant($restaurant->id)
            ->shownToGuests()
            ->withVisibleParents()
            ->count());

        $restaurant->setAttribute('reviews_summary', $this->reviews->summary($restaurant));
        $restaurant->setAttribute(
            'pending_reviews',
            $this->reviews->counts($restaurant)[RestaurantReview::STATUS_PENDING],
        );

        return $restaurant->setRelation('versions', $this->versions->ofRestaurant($restaurant, true));
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
     * Upload a photo of the restaurant (of itself or of a dish). It's shown, once it's set
     * as one of the photos; till then it's unattached.
     *
     * @param Restaurant $restaurant
     * @param UploadedFile $file
     *
     * @return Media
     * @throws FileNotFoundException
     */
    public function uploadPhoto(Restaurant $restaurant, UploadedFile $file): Media
    {
        return $this->media->create([
            'file' => $file,
            // the original name of the file is the photo's title
            'name' => $file->getClientOriginalName(),
            'disk' => config('media.disk'),
            'folder' => config('media.folder'),
            'restaurant_id' => $restaurant->id,
        ]);
    }

    /**
     * Set photos of the restaurant, in their order (the first one shown is the cover),
     * each one shown or hidden: `[['id' => 4, 'is_hidden' => false], ...]`.
     *
     * @param Restaurant $restaurant
     * @param array $photos
     *
     * @return void
     */
    public function setPhotos(Restaurant $restaurant, array $photos): void
    {
        $restaurant->setMediaWithVisibility($photos);

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
