<?php

namespace App\Filament\Tables;

use App\Enums\ProductFlag;
use App\Filament\Resources\DishCategoryResource;
use App\Filament\Resources\DishMenuResource;
use App\Filament\Resources\DishResource;
use App\Filament\Resources\DishVariantResource;
use App\Helpers\VersionFields;
use App\Models\BaseModel;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use App\Models\Restaurant;
use App\Repositories\Editor\VersionEditorRepository;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Class ScheduledChangesTable.
 *
 * Columns and actions for scheduled changes: the changes of a record in scheduled versions
 * (the "Scheduled changes" tab of menus, categories, dishes and sizes) and the versions
 * themselves (the global "Scheduled changes" page and the dashboard widget).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ScheduledChangesTable
{
    /**
     * Changed model classes with their resources and labels.
     *
     * @var array<class-string, array{resource: class-string, label: string}>
     */
    public const MODELS = [
        DishMenu::class => ['resource' => DishMenuResource::class, 'label' => 'Menu'],
        DishCategory::class => ['resource' => DishCategoryResource::class, 'label' => 'Category'],
        Dish::class => ['resource' => DishResource::class, 'label' => 'Dish'],
        DishVariant::class => ['resource' => DishVariantResource::class, 'label' => 'Size'],
    ];

    /**
     * Columns of a record's change: its version, what changes, when and the version's status.
     *
     * @return array
     */
    public static function changeColumns(): array
    {
        return [
            TextColumn::make('changes')
                ->state(fn (MenuVersionChange $record) => static::describeChange($record))
                ->listWithLineBreaks(),
            TextColumn::make('version.name')
                ->label('Version')
                ->state(fn (MenuVersionChange $record) => static::getVersionName($record->version))
                ->color(fn (MenuVersionChange $record) => $record->version->name ? null : 'gray'),
            static::goesLiveColumn('version.goes_live_at', fn (MenuVersionChange $record) => $record->version),
            static::statusColumn('version.status', fn (MenuVersionChange $record) => $record->version),
        ];
    }

    /**
     * Columns of a version: what it changes, its restaurant, when and its status.
     *
     * @return array
     */
    public static function versionColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('What')
                ->state(fn (MenuVersion $record) => static::describeVersion($record))
                ->listWithLineBreaks(),
            TextColumn::make('restaurant.name')
                ->label('Restaurant')
                ->visible(fn () => !request()->user()?->restaurant_id),
            static::goesLiveColumn('goes_live_at', fn (MenuVersion $record) => $record),
            static::statusColumn('status', fn (MenuVersion $record) => $record),
            TextColumn::make('creator.name')
                ->label('By')
                ->placeholder('—')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Column of the time a version goes live, in its restaurant's time zone.
     *
     * @param string $name
     * @param callable $version returns the version of the row
     *
     * @return TextColumn
     */
    protected static function goesLiveColumn(string $name, callable $version): TextColumn
    {
        return TextColumn::make($name)
            ->label('When')
            ->dateTime('D, j M Y, H:i')
            ->timezone(fn (Model $record) => $version($record)->restaurant?->timezone ?: null)
            ->placeholder('Not scheduled')
            ->sortable();
    }

    /**
     * Column of a version's status, with the reason of a failure.
     *
     * @param string $name
     * @param callable $version returns the version of the row
     *
     * @return TextColumn
     */
    protected static function statusColumn(string $name, callable $version): TextColumn
    {
        return TextColumn::make($name)
            ->label('Status')
            ->badge()
            ->formatStateUsing(fn (string $state) => Str::ucfirst($state))
            ->color(fn (string $state) => match ($state) {
                MenuVersion::STATUS_APPLIED => 'success',
                MenuVersion::STATUS_FAILED => 'danger',
                MenuVersion::STATUS_INACTIVE => 'warning',
                MenuVersion::STATUS_DRAFT => 'gray',
                default => 'info',
            })
            ->tooltip(fn (Model $record) => $version($record)->failure_reason);
    }

    /**
     * Actions of a version (or of the version of a record's change): apply it now, deactivate
     * or activate it.
     *
     * @param callable|null $version returns the version of the row (the row itself, if not given)
     *
     * @return Action[]
     */
    public static function versionActions(?callable $version = null): array
    {
        $version ??= fn (Model $record) => $record;

        return [
            Action::make('apply')
                ->label('Apply now')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->modalDescription(fn (Model $record) => static::describeApplying($version($record)))
                ->visible(fn (Model $record) => $version($record)->isPending()
                    && Gate::allows('update', $version($record)))
                ->action(function (Model $record) use ($version) {
                    $subject = $version($record);
                    $applied = app(VersionEditorRepository::class)->apply($subject);

                    Notification::make()
                        ->title($applied ? 'The changes were applied' : 'The changes couldn\'t be applied')
                        ->body($applied ? null : $subject->failure_reason)
                        ->status($applied ? 'success' : 'danger')
                        ->send();
                }),
            Action::make('deactivate')
                ->label('Deactivate')
                ->icon('heroicon-o-pause')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('It keeps its date, but doesn\'t go live until it\'s activated again.')
                ->visible(fn (Model $record) => $version($record)->status === MenuVersion::STATUS_SCHEDULED
                    && Gate::allows('update', $version($record)))
                ->action(fn (Model $record) => app(VersionEditorRepository::class)->deactivate($version($record))),
            Action::make('activate')
                ->label('Activate')
                ->icon('heroicon-o-clock')
                ->visible(fn (Model $record) => $version($record)->status === MenuVersion::STATUS_INACTIVE
                    && Gate::allows('update', $version($record)))
                ->action(fn (Model $record) => static::attempt(
                    fn () => app(VersionEditorRepository::class)->activate($version($record))
                )),
        ];
    }

    /**
     * Action, which removes a record's change from its version (with the version, when it was
     * its only change).
     *
     * @return Action
     */
    public static function removeAction(): Action
    {
        return Action::make('remove')
            ->label('Cancel')
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Cancel the scheduled change')
            ->modalDescription('The record stays as it is. Other changes of the version still go live.')
            ->visible(fn (MenuVersionChange $record) => $record->version->isPending()
                && Gate::allows('update', $record->version))
            ->action(function (MenuVersionChange $record) {
                $version = $record->version;

                static::attempt(function () use ($version, $record) {
                    app(VersionEditorRepository::class)->removeChange($version, $record);

                    if (!$version->itemChanges()->exists()) {
                        $version->delete();
                    }

                    Notification::make()
                        ->title('The scheduled change was cancelled')
                        ->success()
                        ->send();
                });
            });
    }

    /**
     * Header action of the relation manager, which schedules a change of its owner record
     * (a version of one change).
     *
     * @return Action
     */
    public static function scheduleAction(): Action
    {
        return Action::make('schedule')
            ->label('Schedule change')
            ->icon('heroicon-o-clock')
            ->modalHeading('Schedule a change')
            ->modalDescription('Change the values that should be applied at the given time.')
            ->visible(fn (RelationManager $livewire) => Gate::allows('update', $livewire->getOwnerRecord()))
            ->form(fn (RelationManager $livewire) => [
                DateTimePicker::make('goes_live_at')
                    ->label('When')
                    ->timezone(static::getTimezone($livewire->getOwnerRecord()))
                    ->seconds(false)
                    ->after('now')
                    ->required(),
                Section::make('New values')
                    ->schema(static::getSchedulableFields($livewire))
                    ->columns(2),
            ])
            ->fillForm(fn (RelationManager $livewire) => array_merge(
                static::getCurrentValues($livewire),
                ['goes_live_at' => static::getDefaultGoesLiveAt($livewire->getOwnerRecord())],
            ))
            ->action(function (array $data, RelationManager $livewire, Action $action) {
                /** @var BaseModel $record */
                $record = $livewire->getOwnerRecord();
                $values = Arr::only($data, array_keys(static::getCurrentValues($livewire)));

                try {
                    $version = app(VersionEditorRepository::class)->scheduleChange(
                        $record,
                        $values,
                        Carbon::parse($data['goes_live_at'], config('app.timezone')),
                        request()->user(),
                    );
                } catch (ValidationException $exception) {
                    Notification::make()
                        ->title(Arr::first(Arr::flatten($exception->errors())))
                        ->danger()
                        ->send();

                    $action->halt();

                    return;
                }

                if (!$version) {
                    Notification::make()
                        ->title('Nothing to schedule')
                        ->body('Change at least one of the values.')
                        ->warning()
                        ->send();

                    $action->halt();
                }

                Notification::make()
                    ->title('The change was scheduled')
                    ->success()
                    ->send();
            });
    }

    /**
     * Column, which shows an icon when there are scheduled (not yet applied) changes.
     * Requires the query to be modified with `withScheduledChangesCount()`.
     *
     * @return IconColumn
     */
    public static function scheduledColumn(): IconColumn
    {
        return IconColumn::make('scheduled_changes_count')
            ->label('Scheduled')
            ->alignCenter()
            ->icon(fn ($state) => (int) $state > 0 ? 'heroicon-o-clock' : null)
            ->color('info')
            ->tooltip(fn ($state) => (int) $state > 0 ? Str::plural("$state scheduled change", (int) $state) : null);
    }

    /**
     * Add the count of the records' changes in versions, which haven't gone live, to the query.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public static function withScheduledChangesCount(Builder $query): Builder
    {
        return $query->withCount('pendingScheduledChanges as scheduled_changes_count');
    }

    /**
     * Name of the version, or what it changes when it's a change scheduled on its own.
     *
     * @param MenuVersion $version
     *
     * @return string
     */
    public static function getVersionName(MenuVersion $version): string
    {
        return $version->name ?? 'On its own';
    }

    /**
     * What the version changes: its name and counts, or its only change.
     *
     * @param MenuVersion $version
     *
     * @return string[]
     */
    public static function describeVersion(MenuVersion $version): array
    {
        $changes = $version->itemChanges;
        $count = $version->countChanges();
        $items = $version->countItems();

        if (!$version->name && $items === 1) {
            /** @var MenuVersionChange $change */
            $change = $changes->first();

            return [static::getSubjectLabel($change), ...static::describeChange($change)];
        }

        return [
            $version->name ?? 'Changes',
            Str::plural("$count change", $count) . ' in ' . Str::plural("$items item", $items),
        ];
    }

    /**
     * Human-readable list of the change's fields, with live values of pending ones.
     *
     * @param MenuVersionChange $change
     *
     * @return string[]
     */
    public static function describeChange(MenuVersionChange $change): array
    {
        if ($change->isNew()) {
            return ['New ' . Str::lower(static::getTypeLabel($change->target_type))];
        }

        $target = $change->version->isPending() ? $change->target : null;
        $kinds = MenuVersionChange::fieldsOf($change->target_type);
        $lines = [];

        foreach ($change->fields as $field => $values) {
            $kind = $kinds[$field] ?? MenuVersionChange::KIND_VALUE;
            // shown as "Live", like in the tables and forms
            $label = $field === 'is_hidden' ? 'Live' : Str::headline($field);
            $new = static::formatValue($kind, $values['new'], $target);
            $line = "$label: $new";

            if ($target) {
                $live = static::formatValue($kind, VersionFields::live($target, $field, $kind), $target);

                if ($live !== $new) {
                    $line .= " (now: $live)";
                }
            }

            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Label of the changed record.
     *
     * @param MenuVersionChange $change
     *
     * @return string
     */
    public static function getSubjectLabel(MenuVersionChange $change): string
    {
        $label = static::getTypeLabel($change->target_type);
        $target = $change->target;

        if ($change->isNew()) {
            return "New $label";
        }

        if (!$target || $target->getAttribute('deleted_at')) {
            return "$label #{$change->target_id} (deleted)";
        }

        if ($target instanceof DishVariant) {
            $details = trim($target->weight . ' ' . $target->weight_unit);

            return "$label · {$target->dish->title}" . ($details ? " ($details)" : '');
        }

        return "$label · " . ($target->getAttribute('title') ?? $target->getAttribute('name')
            ?? $target->getAttribute('text'));
    }

    /**
     * Label of a type of records.
     *
     * @param string $type
     *
     * @return string
     */
    protected static function getTypeLabel(string $type): string
    {
        $class = MenuVersionChange::TARGETS[$type]['class'] ?? null;

        return static::MODELS[$class]['label'] ?? Str::headline(Str::singular($type));
    }

    /**
     * Confirmation of applying a version now.
     *
     * @param MenuVersion $version
     *
     * @return string
     */
    protected static function describeApplying(MenuVersion $version): string
    {
        $count = $version->countChanges();

        return $version->countItems() > 1 || $version->name
            ? 'All ' . Str::plural("$count change", $count) . ' of "' . static::getVersionName($version)
                . '" are applied right away, instead of at their scheduled time.'
            : 'The change is applied right away, instead of at its scheduled time.';
    }

    /**
     * Format a value of the field kind for display.
     *
     * @param string $kind
     * @param mixed $value
     * @param BaseModel|null $target
     *
     * @return string
     */
    protected static function formatValue(string $kind, mixed $value, ?BaseModel $target): string
    {
        $value = VersionFields::normalize($kind, $value);

        return match ($kind) {
            MenuVersionChange::KIND_TEXT => Str::limit((string) static::defaultText($value, $target), 60) ?: '—',
            // "Live" is the other way around
            MenuVersionChange::KIND_HIDDEN => $value ? 'No' : 'Yes',
            MenuVersionChange::KIND_ARCHIVED => $value ? 'Yes' : 'No',
            MenuVersionChange::KIND_FLAGS => implode(', ', array_map(
                fn ($flag) => ProductFlag::getLabels()[$flag] ?? $flag,
                $value
            )) ?: '—',
            MenuVersionChange::KIND_MEDIA => Str::plural(count($value) . ' photo', count($value)),
            MenuVersionChange::KIND_SIZES => Str::plural(count($value) . ' size', count($value)),
            default => $value === null ? '—' : Str::limit((string) $value, 60),
        };
    }

    /**
     * The text in the record's default language (or another one, when it has none in it).
     *
     * @param array $texts
     * @param BaseModel|null $target
     *
     * @return string|null
     */
    protected static function defaultText(array $texts, ?BaseModel $target): ?string
    {
        $locale = $target instanceof TranslatableInterface ? $target->getDefaultLocale() : null;
        $texts = array_filter($texts);

        return $texts[$locale] ?? (reset($texts) ?: null);
    }

    /**
     * Run an action, which may be invalid: its problem is shown as a notification.
     *
     * @param callable $callback
     *
     * @return mixed what the callback returns, null when it was invalid
     */
    protected static function attempt(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (ValidationException $exception) {
            Notification::make()
                ->title(Arr::first(Arr::flatten($exception->errors())))
                ->danger()
                ->send();

            return null;
        }
    }

    /**
     * Fields of the owner record, which can be scheduled.
     *
     * @param RelationManager $livewire
     *
     * @return array
     */
    protected static function getSchedulableFields(RelationManager $livewire): array
    {
        /** @var class-string $page */
        $page = $livewire->getPageClass();

        return $page::getResource()::getSchedulableFields();
    }

    /**
     * Current values of the owner record's schedulable fields.
     * Fields, which aren't saved (e.g. the separate inputs of flags), are skipped.
     *
     * @param RelationManager $livewire
     *
     * @return array
     */
    protected static function getCurrentValues(RelationManager $livewire): array
    {
        $record = $livewire->getOwnerRecord();
        $values = [];

        foreach (static::getSchedulableFields($livewire) as $field) {
            if (!$field instanceof Field || !$field->isDehydrated()) {
                continue;
            }

            $name = $field->getName();
            $values[$name] = $record->getAttribute($name);
        }

        return $values;
    }

    /**
     * Timezone of the record's restaurant.
     *
     * @param Model $record
     *
     * @return string
     */
    public static function getTimezone(Model $record): string
    {
        $restaurantId = $record instanceof BaseModel ? $record->getRestaurantId() : null;
        $restaurant = $restaurantId ? Restaurant::query()->find($restaurantId) : null;

        return $restaurant?->timezone ?: config('app.timezone');
    }

    /**
     * Next Monday at midnight in the restaurant's timezone (in the app's timezone).
     *
     * @param Model $record
     *
     * @return string
     */
    protected static function getDefaultGoesLiveAt(Model $record): string
    {
        return Carbon::now(static::getTimezone($record))
            ->next(Carbon::MONDAY)
            ->setTimezone(config('app.timezone'))
            ->toDateTimeString();
    }
}
