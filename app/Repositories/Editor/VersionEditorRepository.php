<?php

namespace App\Repositories\Editor;

use App\Helpers\VersionFields;
use App\Models\BaseModel;
use App\Models\DishVariant;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use App\Models\Restaurant;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Class VersionEditorRepository.
 *
 * Scheduled versions: named sets of changes, which go live together at one date and time.
 * Each change keeps the live values from when it was planned, to notice when they change.
 * Nothing reaches guests until the version is applied (see `VersionApplier`).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class VersionEditorRepository
{
    /**
     * VersionEditorRepository constructor.
     *
     * @param VersionApplier $applier
     */
    public function __construct(protected VersionApplier $applier)
    {
    }

    /**
     * Versions of the restaurant: pending ones by their date, then the ones, which went live.
     *
     * @param Restaurant $restaurant
     * @param bool $pending only the ones, which haven't gone live
     *
     * @return Collection<int, MenuVersion>
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function ofRestaurant(Restaurant $restaurant, bool $pending = false): Collection
    {
        /** @var Collection<int, MenuVersion> $versions */
        $versions = MenuVersion::query()
            ->ofRestaurant($restaurant->id)
            ->when($pending, fn ($query) => $query->pending())
            ->inTimeline()
            ->with(['creator', 'itemChanges'])
            ->get();

        foreach ($versions as $version) {
            $version->setRelation('restaurant', $restaurant);
        }

        return $this->withLabels($versions);
    }

    /**
     * Create a version: a draft, unless it's scheduled right away (with its date).
     *
     * @param Restaurant $restaurant
     * @param array $data `name`, `goes_live_at`, `schedule` and `changes`
     * @param User|null $user who creates it
     *
     * @return MenuVersion
     */
    public function create(Restaurant $restaurant, array $data, ?User $user): MenuVersion
    {
        return DB::transaction(function () use ($restaurant, $data, $user) {
            /** @var MenuVersion $version */
            $version = MenuVersion::query()->create([
                'restaurant_id' => $restaurant->id,
                'name' => VersionFields::value($data['name'] ?? null),
                'goes_live_at' => $this->goesLiveAt($restaurant, $data['goes_live_at'] ?? null),
                'created_by' => $user?->id,
                'status' => MenuVersion::STATUS_DRAFT,
            ]);

            foreach ($data['changes'] ?? [] as $change) {
                $this->putChange($version, $change);
            }

            if (!empty($data['schedule'])) {
                $this->schedule($version);
            }

            return $version;
        });
    }

    /**
     * Schedule a change of an item on its own: a version of one change. Texts may be given in the
     * item's default language only (like the admin panel shows them), the others stay.
     *
     * @param BaseModel $target
     * @param array $values new values of its fields
     * @param CarbonInterface $goesLiveAt
     * @param User|null $user who schedules it
     *
     * @return MenuVersion|null the version, none when nothing would change
     */
    public function scheduleChange(
        BaseModel $target,
        array $values,
        CarbonInterface $goesLiveAt,
        ?User $user
    ): ?MenuVersion {
        $type = $target->getMorphClass();
        $kinds = MenuVersionChange::fieldsOf($type);
        $fields = [];

        foreach (Arr::only($values, array_keys($kinds)) as $field => $value) {
            if ($kinds[$field] === MenuVersionChange::KIND_TEXT && !is_array($value)) {
                /** @var BaseModel&TranslatableInterface $target */
                $value = [
                    ...VersionFields::live($target, $field, MenuVersionChange::KIND_TEXT),
                    $target->getDefaultLocale() => $value,
                ];
            }

            $fields[$field] = $value;
        }

        return DB::transaction(function () use ($target, $type, $fields, $goesLiveAt, $user) {
            /** @var MenuVersion $version */
            $version = MenuVersion::query()->create([
                'restaurant_id' => $target->getRestaurantId(),
                'status' => MenuVersion::STATUS_SCHEDULED,
                'goes_live_at' => $goesLiveAt->clone()->setTimezone(config('app.timezone'))->startOfMinute(),
                'created_by' => $user?->id,
            ]);

            $change = $this->putChange($version, [
                'target_type' => $type,
                'target_id' => $target->getKey(),
                'fields' => $fields,
            ]);

            if (!$change) {
                $version->delete();

                return null;
            }

            return $version;
        });
    }

    /**
     * Rename or reschedule the version. A scheduled one has to stay in the future.
     *
     * @param MenuVersion $version
     * @param array $data `name` and `goes_live_at`
     *
     * @return MenuVersion
     * @throws ValidationException
     */
    public function update(MenuVersion $version, array $data): MenuVersion
    {
        $this->assertPending($version);

        if (array_key_exists('name', $data)) {
            $version->name = VersionFields::value($data['name']);
        }

        if (array_key_exists('goes_live_at', $data)) {
            $version->goes_live_at = $this->goesLiveAt($version->restaurant, $data['goes_live_at']);

            if ($version->status === MenuVersion::STATUS_SCHEDULED) {
                $this->assertInFuture($version);
            }
        }

        $version->save();

        return $version;
    }

    /**
     * Put a change of an item into the version: its fields are merged into the item's change.
     * A field, which is back at its live value, isn't a change anymore; a change without
     * fields is removed (a new item stays, until it's removed itself).
     *
     * @param MenuVersion $version
     * @param array $data `id` (of the change, for a new item), `target_type`, `target_id`,
     * `parent_id` (of a new item), `fields` (new values) and `revert` (fields to leave as they are)
     *
     * @return MenuVersionChange|null the change, none when it was removed
     * @throws ValidationException
     */
    public function putChange(MenuVersion $version, array $data): ?MenuVersionChange
    {
        $this->assertPending($version);

        return DB::transaction(function () use ($version, $data) {
            $change = $this->findChange($version, $data);
            $type = $change->target_type;
            $kinds = MenuVersionChange::fieldsOf($type);
            $target = $change->isNew() ? null : $this->findTarget($version, $type, $change->target_id);
            $fields = Arr::except($change->fields ?? [], $data['revert'] ?? []);

            foreach ($data['fields'] ?? [] as $field => $value) {
                $kind = $kinds[$field];
                $new = VersionFields::normalize($kind, $value);
                // the live value from when the field was planned
                $live = array_key_exists($field, $fields)
                    ? $fields[$field]['live']
                    : ($target ? VersionFields::live($target, $field, $kind) : null);

                if ($target && VersionFields::same($kind, $live, $new)) {
                    unset($fields[$field]);
                    continue;
                }

                $fields[$field] = ['live' => $live, 'new' => $new];
            }

            $change->fields = $fields;

            if (!$change->isNew() && empty($fields)) {
                if ($change->exists) {
                    $change->delete();
                }

                $this->assertDishKeepsShownSize($version, $change);

                return null;
            }

            $this->assertComplete($version, $change);
            $change->save();
            $this->assertDishKeepsShownSize($version, $change);

            return $change;
        });
    }

    /**
     * Remove the change from the version: the item stays as it is.
     *
     * @param MenuVersion $version
     * @param MenuVersionChange $change
     *
     * @return void
     * @throws ValidationException
     */
    public function removeChange(MenuVersion $version, MenuVersionChange $change): void
    {
        $this->assertPending($version);

        DB::transaction(function () use ($version, $change) {
            $change->delete();

            $this->assertDishKeepsShownSize($version, $change);
        });
    }

    /**
     * Schedule the version at its date, which has to be in the future.
     *
     * @param MenuVersion $version
     *
     * @return MenuVersion
     * @throws ValidationException
     */
    public function schedule(MenuVersion $version): MenuVersion
    {
        $this->assertPending($version);
        $this->assertInFuture($version);

        $version->forceFill([
            'status' => MenuVersion::STATUS_SCHEDULED,
            'failed_at' => null,
            'failure_reason' => null,
        ])->save();

        return $version;
    }

    /**
     * Deactivate the scheduled version: it keeps its date, but doesn't go live.
     *
     * @param MenuVersion $version
     *
     * @return MenuVersion
     * @throws ValidationException
     */
    public function deactivate(MenuVersion $version): MenuVersion
    {
        if ($version->status !== MenuVersion::STATUS_SCHEDULED) {
            throw ValidationException::withMessages(['status' => 'Only scheduled versions can be deactivated.']);
        }

        $version->forceFill(['status' => MenuVersion::STATUS_INACTIVE])->save();

        return $version;
    }

    /**
     * Activate the inactive version: it's scheduled again (its date has to be in the future).
     *
     * @param MenuVersion $version
     *
     * @return MenuVersion
     * @throws ValidationException
     */
    public function activate(MenuVersion $version): MenuVersion
    {
        if ($version->status !== MenuVersion::STATUS_INACTIVE) {
            throw ValidationException::withMessages(['status' => 'Only inactive versions can be activated.']);
        }

        return $this->schedule($version);
    }

    /**
     * Apply the version now, instead of at its date.
     *
     * @param MenuVersion $version
     *
     * @return bool whether it went live (otherwise it failed, with the reason)
     * @throws ValidationException
     */
    public function apply(MenuVersion $version): bool
    {
        $this->assertPending($version);

        return $this->applier->apply($version);
    }

    /**
     * Copy the version as a draft with the same date and changes.
     *
     * @param MenuVersion $version
     * @param User|null $user who copies it
     *
     * @return MenuVersion
     */
    public function duplicate(MenuVersion $version, ?User $user): MenuVersion
    {
        return DB::transaction(function () use ($version, $user) {
            /** @var MenuVersion $copy */
            $copy = MenuVersion::query()->create([
                'restaurant_id' => $version->restaurant_id,
                'name' => $version->name ? "$version->name (copy)" : null,
                'goes_live_at' => $version->goes_live_at,
                'created_by' => $user?->id,
                'status' => MenuVersion::STATUS_DRAFT,
            ]);

            foreach ($version->itemChanges as $change) {
                $copy->itemChanges()->create(Arr::only($change->getAttributes(), [
                    'target_type',
                    'target_id',
                    'parent_id',
                ]) + ['fields' => $change->fields]);
            }

            return $copy;
        });
    }

    /**
     * Delete the version with its changes.
     *
     * @param MenuVersion $version
     *
     * @return void
     */
    public function delete(MenuVersion $version): void
    {
        $version->delete();
    }

    /**
     * Load what the version page shows: its changes with their items' labels and conflicts.
     *
     * @param MenuVersion $version
     *
     * @return MenuVersion
     */
    public function load(MenuVersion $version): MenuVersion
    {
        $version->load(['restaurant', 'creator', 'itemChanges']);
        $this->withLabels(collect([$version]));

        foreach ($version->itemChanges as $change) {
            $change->conflicts = $this->conflicts($version, $change);
        }

        return $version;
    }

    /**
     * Conflicts of the change: fields, whose live value has changed since they were planned
     * (with the live value now), or `missing`, when the item (or the parent of a new one) is gone.
     *
     * @param MenuVersion $version
     * @param MenuVersionChange $change
     *
     * @return array
     */
    public function conflicts(MenuVersion $version, MenuVersionChange $change): array
    {
        if ($change->isNew()) {
            $parentType = MenuVersionChange::TARGETS[$change->target_type]['parent'];

            return $this->item($version, $parentType, $change->parent_id) ? [] : ['missing' => true];
        }

        $target = $this->item($version, $change->target_type, $change->target_id);

        if (!$target) {
            return ['missing' => true];
        }

        $kinds = MenuVersionChange::fieldsOf($change->target_type);
        $fields = [];

        foreach ($change->fields as $field => $values) {
            $live = VersionFields::live($target, $field, $kinds[$field]);

            if (!VersionFields::same($kinds[$field], $live, $values['live'])) {
                $fields[$field] = $live;
            }
        }

        return $fields ? ['fields' => $fields] : [];
    }

    /**
     * Find the change the data is about: the given one (a new item's), the change of the given
     * item, or a new one.
     *
     * @param MenuVersion $version
     * @param array $data
     *
     * @return MenuVersionChange
     * @throws ValidationException
     */
    protected function findChange(MenuVersion $version, array $data): MenuVersionChange
    {
        $type = $data['target_type'];

        if (!empty($data['id'])) {
            /** @var MenuVersionChange|null $change */
            $change = $version->itemChanges()->find($data['id']);

            if (!$change || $change->target_type !== $type) {
                throw ValidationException::withMessages(['id' => 'The change isn\'t in this version.']);
            }

            return $change;
        }

        $targetId = $data['target_id'] ?? null;

        if ($targetId) {
            $this->findTarget($version, $type, $targetId);

            /** @var MenuVersionChange|null $change */
            $change = $version->itemChanges()
                ->where('target_type', $type)
                ->where('target_id', $targetId)
                ->first();

            return $change ?? new MenuVersionChange([
                'version_id' => $version->id,
                'target_type' => $type,
                'target_id' => $targetId,
                'fields' => [],
            ]);
        }

        $parentType = MenuVersionChange::TARGETS[$type]['parent'] ?? null;

        if (!$parentType) {
            throw ValidationException::withMessages(['target_id' => 'This item can\'t be added in a version.']);
        }

        if (!$this->item($version, $parentType, (int) ($data['parent_id'] ?? 0))) {
            throw ValidationException::withMessages(['parent_id' => 'Pick where the new item goes.']);
        }

        return new MenuVersionChange([
            'version_id' => $version->id,
            'target_type' => $type,
            'parent_id' => (int) $data['parent_id'],
            'fields' => [],
        ]);
    }

    /**
     * The item of the version's restaurant (whatever its state, but not deleted).
     *
     * @param MenuVersion $version
     * @param string $type
     * @param int $id
     *
     * @return BaseModel
     * @throws ValidationException
     */
    protected function findTarget(MenuVersion $version, string $type, int $id): BaseModel
    {
        $target = $this->item($version, $type, $id);

        if (!$target) {
            throw ValidationException::withMessages(['target_id' => 'The item doesn\'t exist anymore.']);
        }

        return $target;
    }

    /**
     * The item, if it exists (it isn't deleted) and belongs to the version's restaurant.
     *
     * @param MenuVersion $version
     * @param string|null $type
     * @param int|null $id
     *
     * @return BaseModel|null
     */
    protected function item(MenuVersion $version, ?string $type, ?int $id): ?BaseModel
    {
        return MenuVersionChange::findItem($version->restaurant_id, $type, $id);
    }

    /**
     * A new item needs what it can't be created without: a name (in the default language)
     * and sizes for a dish, a price for a size, a text for a note.
     *
     * @param MenuVersion $version
     * @param MenuVersionChange $change
     *
     * @return void
     * @throws ValidationException
     */
    protected function assertComplete(MenuVersion $version, MenuVersionChange $change): void
    {
        if (!$change->isNew()) {
            return;
        }

        $values = $change->newValues();
        $default = $version->restaurant->getDefaultLocale();

        $missing = match ($change->target_type) {
            'dishes' => match (true) {
                empty($values['title'][$default]) => ['fields.title' => 'A new dish needs a name.'],
                empty($values['sizes']) => ['fields.sizes' => 'A new dish needs a size.'],
                default => [],
            },
            'dish-variants' => ($values['price'] ?? null) === null
                ? ['fields.price' => 'A new size needs a price.']
                : [],
            'restaurant-notes' => empty($values['text'][$default])
                ? ['fields.text' => 'A new note needs a text.']
                : [],
            default => [],
        };

        if ($missing) {
            throw ValidationException::withMessages($missing);
        }
    }

    /**
     * Make sure the dish of a size keeps a size shown to guests, once the version goes live.
     *
     * @param MenuVersion $version
     * @param MenuVersionChange $change
     *
     * @return void
     * @throws ValidationException
     */
    protected function assertDishKeepsShownSize(MenuVersion $version, MenuVersionChange $change): void
    {
        if ($change->target_type !== 'dish-variants') {
            return;
        }

        $dishId = (int) ($change->isNew()
            ? $change->parent_id
            : DishVariant::query()->withoutGlobalScopes()->whereKey($change->target_id)->value('dish_id'));

        /** @var Collection<int, MenuVersionChange> $changes */
        $changes = $version->itemChanges()->where('target_type', 'dish-variants')->get();
        $planned = $changes->whereNotNull('target_id')->keyBy('target_id');
        $shown = 0;

        /** @var Collection<int, DishVariant> $sizes */
        $sizes = DishVariant::query()
            ->withoutGlobalScopes()
            ->where('dish_id', $dishId)
            ->whereNull('deleted_at')
            ->get();

        foreach ($sizes as $size) {
            $values = $planned->get($size->id)?->newValues() ?? [];

            if (!($values['is_hidden'] ?? $size->is_hidden) && !($values['archived'] ?? $size->archived)) {
                $shown++;
            }
        }

        $shown += $changes
            ->filter(fn (MenuVersionChange $item) => $item->isNew() && (int) $item->parent_id === $dishId)
            ->reject(fn (MenuVersionChange $item) => $item->newValues()['is_hidden'] ?? false)
            ->count();

        if ($shown === 0) {
            throw ValidationException::withMessages(['fields' => DishVariant::LAST_SIZE_MESSAGE]);
        }
    }

    /**
     * Date and time, at which the version goes live: given in the restaurant's time zone
     * (unless it has an offset), stored in the app's one.
     *
     * @param Restaurant $restaurant
     * @param string|null $value
     *
     * @return Carbon|null
     */
    protected function goesLiveAt(Restaurant $restaurant, ?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value, $restaurant->timezone ?: config('app.timezone'))
            ->setTimezone(config('app.timezone'))
            ->startOfMinute();
    }

    /**
     * Make sure the version hasn't gone live yet.
     *
     * @param MenuVersion $version
     *
     * @return void
     * @throws ValidationException
     */
    protected function assertPending(MenuVersion $version): void
    {
        if (!$version->isPending()) {
            throw ValidationException::withMessages(['status' => 'The version has already gone live.']);
        }
    }

    /**
     * Make sure the version goes live in the future.
     *
     * @param MenuVersion $version
     *
     * @return void
     * @throws ValidationException
     */
    protected function assertInFuture(MenuVersion $version): void
    {
        if (!$version->goes_live_at || !$version->goes_live_at->isFuture()) {
            throw ValidationException::withMessages(['goes_live_at' => 'Pick a date and time in the future.']);
        }
    }

    /**
     * Give changes of the versions labels of their items (see `VersionLabels`).
     *
     * @param Collection<int, MenuVersion> $versions
     *
     * @return Collection<int, MenuVersion>
     */
    protected function withLabels(Collection $versions): Collection
    {
        foreach ($versions as $version) {
            foreach ($version->itemChanges as $change) {
                $change->setRelation('version', $version);
            }
        }

        $changes = $versions->flatMap(fn (MenuVersion $version) => $version->itemChanges);

        (new VersionLabels())->label($changes);

        return $versions;
    }
}
