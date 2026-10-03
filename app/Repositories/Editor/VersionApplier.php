<?php

namespace App\Repositories\Editor;

use App\Exceptions\VersionNotApplied;
use App\Helpers\VersionFields;
use App\Helpers\WebCacheHelper;
use App\Models\BaseModel;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishVariant;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use App\Models\Restaurant;
use App\Models\RestaurantNote;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class VersionApplier.
 *
 * Applies scheduled versions: all of a version's changes go live in one transaction, or none
 * of them (the version fails, with the reason). New items are created first, then items are
 * changed: what's shown before what's hidden, so that e.g. a dish never loses its last size.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VersionApplier
{
    /**
     * Names of the items in failure reasons, by their type.
     *
     * @var array<string, string>
     */
    protected const NAMES = [
        'restaurants' => 'restaurant',
        'restaurant-notes' => 'note',
        'dish-menus' => 'menu',
        'dish-categories' => 'category',
        'dishes' => 'dish',
        'dish-variants' => 'size',
    ];

    /**
     * VersionApplier constructor.
     *
     * @param DishEditorRepository $dishes
     */
    public function __construct(protected DishEditorRepository $dishes)
    {
    }

    /**
     * Apply the scheduled versions, whose time has come, from the earliest.
     *
     * @return array{applied: int, failed: int}
     */
    public function applyDue(): array
    {
        $counts = ['applied' => 0, 'failed' => 0];

        $ids = MenuVersion::query()
            ->due()
            ->orderBy('goes_live_at')
            ->orderBy('id')
            ->pluck('id');

        foreach ($ids as $id) {
            /** @var MenuVersion|null $version */
            $version = MenuVersion::query()->find($id);

            if ($version && $version->status === MenuVersion::STATUS_SCHEDULED) {
                $counts[$this->apply($version) ? 'applied' : 'failed']++;
            }
        }

        return $counts;
    }

    /**
     * Apply the version: it's applied, or failed with the reason (then nothing of it is applied).
     *
     * @param MenuVersion $version
     *
     * @return bool whether it was applied
     */
    public function apply(MenuVersion $version): bool
    {
        try {
            DB::transaction(function () use ($version) {
                /** @var MenuVersion $locked */
                // @phpstan-ignore-next-line
                $locked = MenuVersion::query()->lockForUpdate()->findOrFail($version->id);

                if (!$locked->isPending()) {
                    throw new VersionNotApplied('The version has already gone live.');
                }

                $this->applyChanges($locked);

                $locked->forceFill([
                    'status' => MenuVersion::STATUS_APPLIED,
                    'applied_at' => Carbon::now(),
                    'failed_at' => null,
                    'failure_reason' => null,
                ])->save();

                $version->setRawAttributes($locked->getAttributes(), true);
            });
        } catch (Throwable $throwable) {
            $version->refresh();

            if ($version->isPending()) {
                $version->forceFill([
                    'status' => MenuVersion::STATUS_FAILED,
                    'failed_at' => Carbon::now(),
                    'failure_reason' => $this->reason($throwable),
                ])->save();
            }

            if (!$throwable instanceof ValidationException && !$throwable instanceof VersionNotApplied) {
                report($throwable);
            }

            return false;
        }

        WebCacheHelper::forgetRestaurants($version->restaurant_id);
        Restaurant::markSaved($version->restaurant_id);

        return true;
    }

    /**
     * Apply changes of the version: new items, then changes of items, which show something,
     * then ones, which hide or archive something.
     *
     * @param MenuVersion $version
     *
     * @return void
     */
    protected function applyChanges(MenuVersion $version): void
    {
        /** @var Collection<int, MenuVersionChange> $changes */
        $changes = $version->itemChanges()->get();
        $targets = [];

        foreach ($changes as $change) {
            if ($change->isNew()) {
                $this->create($version, $change);

                continue;
            }

            $targets[$change->id] = $this->target($version, $change);
        }

        foreach ([false, true] as $hiding) {
            foreach ($changes->reject(fn (MenuVersionChange $change) => $change->isNew()) as $change) {
                $this->update($targets[$change->id], $change, $hiding);
            }
        }
    }

    /**
     * Apply fields of the change to its item: the ones, which hide or archive it, or the others.
     *
     * @param BaseModel $target
     * @param MenuVersionChange $change
     * @param bool $hiding
     *
     * @return void
     */
    protected function update(BaseModel $target, MenuVersionChange $change, bool $hiding): void
    {
        $kinds = MenuVersionChange::fieldsOf($change->target_type);
        $archive = null;
        $media = null;

        foreach ($change->fields as $field => $values) {
            $kind = $kinds[$field];
            $new = $values['new'];
            $hides = in_array($kind, [MenuVersionChange::KIND_HIDDEN, MenuVersionChange::KIND_ARCHIVED]) && $new;

            if ($hides !== $hiding) {
                continue;
            }

            match ($kind) {
                MenuVersionChange::KIND_TEXT => $target instanceof TranslatableInterface
                    ? $target->putTranslations($field, $this->texts($target, $field, $values))
                    : null,
                MenuVersionChange::KIND_ARCHIVED => $archive = (bool) $new,
                MenuVersionChange::KIND_MEDIA => $media = $new,
                default => $target->forceFill([$field => $new]),
            };
        }

        if ($target->isDirty()) {
            $target->save();
        }

        if ($media !== null && $target instanceof MediableInterface) {
            $target->setMediaWithVisibility($media);
        }

        if ($archive !== null && (bool) $target->getAttribute('archived') !== $archive) {
            $archive ? $this->dishes->archive($target) : $this->dishes->unarchive($target);
        }
    }

    /**
     * Texts after the change: only the languages, which the change changes, so that texts
     * written in the others since it was planned are kept.
     *
     * @param TranslatableInterface $target
     * @param string $field
     * @param array{live: mixed, new: mixed} $values
     *
     * @return array<string, string|null>
     */
    protected function texts(TranslatableInterface $target, string $field, array $values): array
    {
        $texts = VersionFields::texts($target->getTranslations($field));
        $live = VersionFields::texts($values['live']);

        foreach (VersionFields::texts($values['new']) as $locale => $text) {
            if ($text !== $live[$locale]) {
                $texts[$locale] = $text;
            }
        }

        return $texts;
    }

    /**
     * Create the new item of the change in its parent.
     *
     * @param MenuVersion $version
     * @param MenuVersionChange $change
     *
     * @return void
     */
    protected function create(MenuVersion $version, MenuVersionChange $change): void
    {
        $values = $change->newValues();
        $parentType = MenuVersionChange::TARGETS[$change->target_type]['parent'];
        $parent = MenuVersionChange::findItem($version->restaurant_id, $parentType, $change->parent_id);

        if (!$parent) {
            throw new VersionNotApplied(sprintf(
                'The %s, where a new %s should be added, doesn\'t exist anymore.',
                static::NAMES[$parentType] ?? 'item',
                static::NAMES[$change->target_type] ?? 'item',
            ));
        }

        match (true) {
            $parent instanceof DishCategory => $this->dishes->create($parent, $values),
            $parent instanceof Dish => DishVariant::query()->create([
                ...Arr::except($values, ['archived']),
                'dish_id' => $parent->id,
            ]),
            $parent instanceof Restaurant => $this->createNote($parent, $values),
            default => null,
        };
    }

    /**
     * Create a note at the end of the restaurant's notes.
     *
     * @param Restaurant $restaurant
     * @param array $values
     *
     * @return void
     */
    protected function createNote(Restaurant $restaurant, array $values): void
    {
        $note = new RestaurantNote([
            'restaurant_id' => $restaurant->id,
            'is_hidden' => (bool) ($values['is_hidden'] ?? false),
            'order' => (int) $restaurant->notes()->max('order') + 1,
        ]);
        $note->putTranslations('text', $values['text'] ?? null);
        $note->save();
    }

    /**
     * The changed item.
     *
     * @param MenuVersion $version
     * @param MenuVersionChange $change
     *
     * @return BaseModel
     */
    protected function target(MenuVersion $version, MenuVersionChange $change): BaseModel
    {
        $target = MenuVersionChange::findItem($version->restaurant_id, $change->target_type, $change->target_id);

        if (!$target) {
            throw new VersionNotApplied(sprintf(
                'A changed %s doesn\'t exist anymore.',
                static::NAMES[$change->target_type] ?? 'item',
            ));
        }

        return $target;
    }

    /**
     * Readable reason of a failure.
     *
     * @param Throwable $throwable
     *
     * @return string
     */
    protected function reason(Throwable $throwable): string
    {
        if ($throwable instanceof ValidationException) {
            return (string) Arr::first(Arr::flatten($throwable->errors()));
        }

        if ($throwable instanceof VersionNotApplied) {
            return $throwable->getMessage();
        }

        return 'Something went wrong while applying it.';
    }
}
