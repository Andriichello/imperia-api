<?php

namespace App\Filament\Tables;

use App\Filament\Resources\DishCategoryResource;
use App\Filament\Resources\DishMenuResource;
use App\Filament\Resources\DishResource;
use App\Filament\Resources\DishVariantResource;
use App\Models\BaseModel;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Morphs\Alteration;
use App\Models\Restaurant;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class AlterationsTable.
 *
 * Table columns and actions for scheduled changes (alterations), shared by the
 * "Scheduled changes" relation manager and the global "Scheduled changes" page.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AlterationsTable
{
    /**
     * Altered model classes with their resources and labels.
     *
     * @var array<class-string, array{resource: class-string, label: string}>
     */
    public const MODELS = [
        DishMenu::class => ['resource' => DishMenuResource::class, 'label' => 'Menu'],
        DishCategory::class => ['resource' => DishCategoryResource::class, 'label' => 'Category'],
        Dish::class => ['resource' => DishResource::class, 'label' => 'Dish'],
        DishVariant::class => ['resource' => DishVariantResource::class, 'label' => 'Variant'],
    ];

    /**
     * Attributes, which should be shown and compared as booleans.
     *
     * @var string[]
     */
    protected const BOOLEANS = ['archived'];

    /**
     * Columns of the altered model and its restaurant (for the global page).
     *
     * @return array
     */
    public static function subjectColumns(): array
    {
        return [
            TextColumn::make('subject')
                ->label('What')
                ->state(fn (Alteration $record) => static::getSubjectLabel($record))
                ->url(fn (Alteration $record) => static::getSubjectUrl($record)),
            TextColumn::make('restaurant.name')
                ->label('Restaurant')
                ->visible(fn () => !request()->user()?->restaurant_id),
        ];
    }

    /**
     * Columns of the change itself.
     *
     * @return array
     */
    public static function columns(): array
    {
        return [
            TextColumn::make('changes')
                ->state(fn (Alteration $record) => static::describeChanges($record))
                ->listWithLineBreaks(),
            TextColumn::make('perform_at')
                ->label('When')
                ->dateTime('d M Y, H:i')
                ->timezone(fn (Alteration $record) => $record->restaurant?->timezone)
                ->placeholder('As soon as possible')
                ->sortable(),
            TextColumn::make('status')
                ->state(fn (Alteration $record) => $record->getStatus())
                ->badge()
                ->formatStateUsing(fn (string $state) => Str::ucfirst($state))
                ->color(fn (string $state) => match ($state) {
                    Alteration::STATUS_DONE => 'success',
                    Alteration::STATUS_FAILED => 'danger',
                    Alteration::STATUS_DUE => 'warning',
                    default => 'info',
                })
                ->tooltip(fn (Alteration $record) => $record->exception
                    ? Str::limit(strtok($record->exception, "\n"), 200)
                    : null),
            TextColumn::make('performed_at')
                ->label('Performed')
                ->dateTime('d M Y, H:i')
                ->timezone(fn (Alteration $record) => $record->restaurant?->timezone)
                ->placeholder('—')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Record actions: run now (also retries failed ones) and cancel.
     *
     * @return array
     */
    public static function actions(): array
    {
        return [
            Action::make('run')
                ->label('Run now')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->modalDescription('The change is applied right away, instead of at its scheduled time.')
                ->authorize('update')
                ->visible(fn (Alteration $record) => $record->getStatus() !== Alteration::STATUS_DONE)
                ->action(function (Alteration $record) {
                    try {
                        $record->perform();

                        Notification::make()
                            ->title('The change was applied')
                            ->success()
                            ->send();
                    } catch (Throwable $throwable) {
                        $record->markAsFailed($throwable);

                        Notification::make()
                            ->title('The change failed')
                            ->body(Str::limit($throwable->getMessage(), 200))
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make()
                ->label('Cancel')
                ->modalHeading('Cancel the scheduled change')
                ->successNotificationTitle('The scheduled change was cancelled')
                ->visible(fn (Alteration $record) => $record->getStatus() !== Alteration::STATUS_DONE),
        ];
    }

    /**
     * Header action of the relation manager to schedule a change of its owner record.
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
            ->visible(fn () => Gate::allows('create', Alteration::class))
            ->form(fn (RelationManager $livewire) => [
                DateTimePicker::make('perform_at')
                    ->label('When')
                    ->timezone(static::getTimezone($livewire->getOwnerRecord()))
                    ->seconds(false)
                    ->after('now')
                    ->required(),
                Section::make('New values')
                    ->schema(static::getAlterableFields($livewire))
                    ->columns(2),
            ])
            ->fillForm(fn (RelationManager $livewire) => array_merge(
                static::getCurrentValues($livewire),
                ['perform_at' => static::getDefaultPerformAt($livewire->getOwnerRecord())],
            ))
            ->action(function (array $data, RelationManager $livewire, Action $action) {
                /** @var BaseModel $record */
                $record = $livewire->getOwnerRecord();
                $changes = static::getChanges($record, $data, array_keys(static::getCurrentValues($livewire)));

                if (empty($changes)) {
                    Notification::make()
                        ->title('Nothing to schedule')
                        ->body('Change at least one of the values.')
                        ->warning()
                        ->send();

                    $action->halt();
                }

                Alteration::query()->create([
                    'alterable_id' => $record->getKey(),
                    'alterable_type' => $record->getMorphClass(),
                    'metadata' => json_encode($changes),
                    'perform_at' => $data['perform_at'],
                ]);

                Notification::make()
                    ->title('The change was scheduled')
                    ->success()
                    ->send();
            });
    }

    /**
     * Column, which shows an icon when there are scheduled (not yet performed) changes.
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
     * Add the count of scheduled (not yet performed or failed) changes to the query.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public static function withScheduledChangesCount(Builder $query): Builder
    {
        return $query->withCount([
            'alterations as scheduled_changes_count' => function (Builder $query) {
                $query->whereNull('performed_at')
                    ->whereNull('failed_at');
            },
        ]);
    }

    /**
     * Human-readable list of changes, with current values of pending ones.
     *
     * @param Alteration $record
     *
     * @return string[]
     */
    public static function describeChanges(Alteration $record): array
    {
        $alterable = $record->alterable;
        $isPending = $record->getStatus() !== Alteration::STATUS_DONE;

        $lines = [];

        foreach ($record->getJson('metadata') as $key => $value) {
            $line = Str::headline($key) . ': ' . static::formatValue($key, $value);

            if ($isPending && $alterable) {
                $current = static::formatValue($key, $alterable->getAttribute($key));

                if ($current !== static::formatValue($key, $value)) {
                    $line .= " (now: $current)";
                }
            }

            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Label of the altered model.
     *
     * @param Alteration $record
     *
     * @return string
     */
    public static function getSubjectLabel(Alteration $record): string
    {
        $alterable = $record->alterable;
        $label = static::MODELS[$alterable ? $alterable::class : null]['label']
            ?? Str::headline($record->alterable_type);

        if (!$alterable) {
            return "$label #{$record->alterable_id} (deleted)";
        }

        if ($alterable instanceof DishVariant) {
            $details = trim($alterable->weight . ' ' . $alterable->weight_unit);

            return "$label · {$alterable->dish->title}" . ($details ? " ($details)" : '');
        }

        return "$label · " . $alterable->getAttribute('title');
    }

    /**
     * Edit page url of the altered model.
     *
     * @param Alteration $record
     *
     * @return string|null
     */
    public static function getSubjectUrl(Alteration $record): ?string
    {
        $alterable = $record->alterable;
        $resource = static::MODELS[$alterable ? $alterable::class : null]['resource'] ?? null;

        if (!$alterable || !$resource || $alterable->getAttribute('deleted_at')) {
            return null;
        }

        return $resource::getUrl('edit', ['record' => $alterable->getKey()]);
    }

    /**
     * Fields of the owner record, which can be scheduled.
     *
     * @param RelationManager $livewire
     *
     * @return array
     */
    protected static function getAlterableFields(RelationManager $livewire): array
    {
        /** @var class-string $page */
        $page = $livewire->getPageClass();

        return $page::getResource()::getAlterableFields();
    }

    /**
     * Current values of the owner record's alterable fields.
     *
     * @param RelationManager $livewire
     *
     * @return array
     */
    protected static function getCurrentValues(RelationManager $livewire): array
    {
        $record = $livewire->getOwnerRecord();
        $values = [];

        foreach (static::getAlterableFields($livewire) as $field) {
            $name = $field->getName();
            $values[$name] = $record->getAttribute($name);
        }

        return $values;
    }

    /**
     * Values from the form, which differ from the current ones.
     *
     * @param BaseModel $record
     * @param array $data
     * @param string[] $keys
     *
     * @return array
     */
    protected static function getChanges(BaseModel $record, array $data, array $keys): array
    {
        $changes = [];

        foreach ($keys as $key) {
            $new = static::normalizeValue($key, $data[$key] ?? null);
            $old = static::normalizeValue($key, $record->getAttribute($key));

            if (!static::isSame($new, $old)) {
                $changes[$key] = $new;
            }
        }

        return $changes;
    }

    /**
     * Compare normalized values: numbers by value (150 = 150.0), everything else strictly.
     *
     * @param mixed $first
     * @param mixed $second
     *
     * @return bool
     */
    protected static function isSame(mixed $first, mixed $second): bool
    {
        if ((is_int($first) || is_float($first)) && (is_int($second) || is_float($second))) {
            return (float) $first === (float) $second;
        }

        return $first === $second;
    }

    /**
     * Normalize a value, so that form and database values can be compared and stored.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected static function normalizeValue(string $key, mixed $value): mixed
    {
        if (in_array($key, static::BOOLEANS)) {
            return (bool) $value;
        }

        if (is_array($value)) {
            return array_values($value);
        }

        if ($value === '' || $value === null) {
            return null;
        }

        if (is_numeric($value) && !is_string($value)) {
            return $value + 0;
        }

        if (is_string($value) && is_numeric($value) && preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            return $value + 0;
        }

        return $value;
    }

    /**
     * Format a value for display.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return string
     */
    protected static function formatValue(string $key, mixed $value): string
    {
        $value = static::normalizeValue($key, $value);

        return match (true) {
            is_bool($value) => $value ? 'Yes' : 'No',
            is_array($value) => $value ? implode(', ', $value) : '—',
            $value === null => '—',
            default => Str::limit((string) $value, 60),
        };
    }

    /**
     * Timezone of the record's restaurant.
     *
     * @param Model $record
     *
     * @return string
     */
    protected static function getTimezone(Model $record): string
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
    protected static function getDefaultPerformAt(Model $record): string
    {
        return Carbon::now(static::getTimezone($record))
            ->next(Carbon::MONDAY)
            ->setTimezone(config('app.timezone'))
            ->toDateTimeString();
    }
}
