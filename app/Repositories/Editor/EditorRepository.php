<?php

namespace App\Repositories\Editor;

use App\Helpers\WebCacheHelper;
use App\Models\BaseModel;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\Morphs\Media;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Class EditorRepository.
 *
 * Changes the admin editor makes. Menus, categories and dishes are hidden (off the site
 * for a while), archived (moved out of the lists, restorable) and deleted only from the archive.
 */
abstract class EditorRepository
{
    /**
     * Put translations of the given attributes, which are in the data
     * (objects by language), the others stay as they are.
     *
     * @param TranslatableInterface $model
     * @param array $data
     * @param string[] $keys
     *
     * @return void
     */
    protected function translate(TranslatableInterface $model, array $data, array $keys): void
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $model->putTranslations($key, $data[$key]);
            }
        }
    }

    /**
     * Move the model to the archive: it's off the site and out of the lists, until it's restored.
     *
     * @param BaseModel $model
     *
     * @return BaseModel
     */
    public function archive(BaseModel $model): BaseModel
    {
        $model->forceFill(['archived' => true, 'archived_at' => Carbon::now()])->save();

        return $model;
    }

    /**
     * Restore the model from the archive.
     *
     * @param BaseModel $model
     *
     * @return BaseModel
     */
    public function unarchive(BaseModel $model): BaseModel
    {
        $model->forceFill(['archived' => false, 'archived_at' => null])->save();

        return $model;
    }

    /**
     * Delete the model (with what's inside it), which is possible only from the archive.
     *
     * @param BaseModel $model
     *
     * @return void
     * @throws ValidationException
     */
    public function deleteArchived(BaseModel $model): void
    {
        if (!$model->getAttribute('archived')) {
            throw ValidationException::withMessages([
                'archived' => 'Only archived items can be deleted. Archive it first.',
            ]);
        }

        DB::transaction(fn () => $model->delete());
    }

    /**
     * Popularity, which puts a new model at the end of the list (lists go from the highest one).
     *
     * @param Builder $query models of the list
     *
     * @return int
     */
    protected function popularityAtTheEnd(Builder $query): int
    {
        $lowest = $query->min($query->qualifyColumn('popularity'));

        return $lowest === null ? 0 : (int) $lowest - 1;
    }

    /**
     * Order models of a list: the first one gets the highest popularity.
     * Only popularity is changed, so the models' events don't fire.
     *
     * @param class-string<BaseModel> $class
     * @param int[] $ids in the new order
     * @param array $values other values to set on all of them (e.g. their new parent)
     *
     * @return void
     */
    protected function order(string $class, array $ids, array $values = []): void
    {
        $popularity = count($ids);

        foreach ($ids as $id) {
            $class::query()
                ->withoutGlobalScopes()
                ->whereKey($id)
                ->update([...$values, 'popularity' => $popularity--]);
        }
    }

    /**
     * Attach photos of a model to its copy, in their order, hidden ones staying hidden.
     *
     * @param MediableInterface $from
     * @param MediableInterface $to
     *
     * @return void
     */
    protected function copyMedia(MediableInterface $from, MediableInterface $to): void
    {
        $photos = [];

        /** @var Media $media */
        foreach ($from->allMedia()->get() as $media) {
            $photos[$media->id] = [
                'order' => data_get($media, 'pivot.order'),
                'is_hidden' => (bool) data_get($media, 'pivot.is_hidden'),
            ];
        }

        $to->allMedia()->attach($photos);
    }

    /**
     * Forget the cached website pages of the restaurant and mark it as saved (e.g. when only
     * popularity or attached photos change, which fire no events).
     *
     * @param int|null $restaurantId
     *
     * @return void
     */
    protected function forgetWebsite(?int $restaurantId): void
    {
        WebCacheHelper::forgetRestaurants($restaurantId);
        Restaurant::markSaved($restaurantId);
    }
}
