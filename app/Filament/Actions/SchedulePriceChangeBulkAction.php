<?php

namespace App\Filament\Actions;

use App\Filament\Tables\ScheduledChangesTable;
use App\Models\BaseModel;
use App\Models\Dish;
use App\Models\DishVariant;
use App\Models\MenuVersion;
use App\Models\Restaurant;
use App\Repositories\Editor\VersionEditorRepository;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * Class SchedulePriceChangeBulkAction.
 *
 * Schedules a price change of the selected dishes (all their sizes) or sizes:
 * a new price, or the current price changed by a percent or an amount.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SchedulePriceChangeBulkAction extends BulkAction
{
    /**
     * Set every price to the given value.
     */
    public const MODE_SET = 'set';

    /**
     * Change every price by the given percent.
     */
    public const MODE_PERCENT = 'percent';

    /**
     * Change every price by the given amount.
     */
    public const MODE_AMOUNT = 'amount';

    /**
     * Get the default name of the action.
     *
     * @return string|null
     */
    public static function getDefaultName(): ?string
    {
        return 'schedulePriceChange';
    }

    /**
     * Set up the action.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Schedule price change')
            ->icon('heroicon-o-clock')
            ->modalHeading('Schedule a price change')
            ->modalDescription('The prices change together. Selected dishes change the prices of all their sizes.')
            ->modalSubmitActionLabel('Schedule')
            ->visible(fn () => Gate::allows('create', MenuVersion::class))
            ->form([
                DateTimePicker::make('goes_live_at')
                    ->label('When')
                    ->helperText("In each restaurant's timezone.")
                    ->seconds(false)
                    ->default(fn () => Carbon::now()->next(Carbon::MONDAY)->toDateTimeString())
                    ->required(),
                ToggleButtons::make('mode')
                    ->label('Price')
                    ->options([
                        self::MODE_PERCENT => 'Change by %',
                        self::MODE_AMOUNT => 'Change by amount',
                        self::MODE_SET => 'Set to',
                    ])
                    ->default(self::MODE_PERCENT)
                    ->inline()
                    ->live()
                    ->required(),
                TextInput::make('value')
                    ->label(fn (Get $get) => match ($get('mode')) {
                        self::MODE_SET => 'New price',
                        self::MODE_AMOUNT => 'Amount',
                        default => 'Percent',
                    })
                    ->helperText(fn (Get $get) => $get('mode') === self::MODE_SET
                        ? null
                        : 'A negative value lowers the prices, e.g. -10.')
                    ->numeric()
                    ->minValue(fn (Get $get) => $get('mode') === self::MODE_SET ? 0 : null)
                    ->required(),
                Select::make('round_to')
                    ->label('Round to')
                    ->options([
                        0 => "Don't round",
                        1 => 'Whole numbers',
                        5 => 'Nearest 5',
                        10 => 'Nearest 10',
                    ])
                    ->default(1)
                    ->selectablePlaceholder(false)
                    ->visible(fn (Get $get) => $get('mode') !== self::MODE_SET),
            ])
            ->action(fn (Collection $records, array $data) => $this->scheduleChanges($records, $data))
            ->deselectRecordsAfterCompletion();
    }

    /**
     * Schedule the price changes: one version per restaurant (in its timezone), with a change
     * of each size, whose price changes. Selected dishes change all their sizes.
     *
     * @param Collection $records
     * @param array $data
     *
     * @return void
     */
    protected function scheduleChanges(Collection $records, array $data): void
    {
        $versions = [];
        $count = 0;
        $skipped = 0;

        /** @var BaseModel $record */
        foreach ($records as $record) {
            // @phpstan-ignore-next-line
            if ($record->trashed() || Gate::denies('update', $record)) {
                $skipped++;
                continue;
            }

            $restaurantId = $record->getRestaurantId();
            $timezone = ScheduledChangesTable::getTimezone($record);
            $goesLiveAt = Carbon::parse($data['goes_live_at'], $timezone);

            if ($goesLiveAt->isPast()) {
                $this->stop('The time has already passed', "In the timezone of {$timezone}.");
            }

            $sizes = $record instanceof Dish
                ? $record->sizes()->where('dish_variants.archived', false)->get()
                : collect([$record]);
            $changed = false;

            /** @var DishVariant $size */
            foreach ($sizes as $size) {
                $current = (float) $size->price;
                $price = static::calculatePrice($current, $data);

                if ($price < 0) {
                    $this->stop('A price would be negative', static::getRecordTitle($record));
                }

                if (abs($price - $current) < 0.005) {
                    continue;
                }

                $versions[$restaurantId]['goes_live_at'] = $goesLiveAt->toIso8601String();
                $versions[$restaurantId]['changes'][] = [
                    'target_type' => $size->getMorphClass(),
                    'target_id' => $size->id,
                    'fields' => ['price' => $price],
                ];
                $changed = true;
                $count++;
            }

            $skipped += $changed ? 0 : 1;
        }

        DB::transaction(function () use ($versions) {
            foreach ($versions as $restaurantId => $version) {
                app(VersionEditorRepository::class)->create(Restaurant::query()->findOrFail($restaurantId), [
                    ...$version,
                    'name' => count($version['changes']) > 1 ? 'Price change' : null,
                    'schedule' => true,
                ], request()->user());
            }
        });

        Notification::make()
            ->title($count ? "Scheduled $count price " . Str::plural('change', $count) : 'Nothing to schedule')
            ->body($skipped ? "Skipped $skipped: the price wouldn't change, or the record is deleted." : null)
            ->status($count ? 'success' : 'warning')
            ->send();
    }

    /**
     * New price from the current one and the form data.
     *
     * @param float $price
     * @param array $data
     *
     * @return float
     */
    public static function calculatePrice(float $price, array $data): float
    {
        $value = (float) $data['value'];

        if ($data['mode'] === self::MODE_SET) {
            return round($value, 2);
        }

        $price = $data['mode'] === self::MODE_PERCENT
            ? $price * (1 + $value / 100)
            : $price + $value;

        $step = (int) ($data['round_to'] ?? 0);

        return $step > 0 ? round($price / $step) * $step : round($price, 2);
    }

    /**
     * Title of a dish, or of a variant's dish.
     *
     * @param BaseModel $record
     *
     * @return string
     */
    protected static function getRecordTitle(BaseModel $record): string
    {
        if ($record instanceof DishVariant) {
            return $record->dish->title . ' (size)';
        }

        return (string) $record->getAttribute('title');
    }

    /**
     * Show an error and keep the modal open (nothing is scheduled).
     *
     * @param string $title
     * @param string $body
     *
     * @return void
     * @throws Halt
     */
    protected function stop(string $title, string $body): void
    {
        Notification::make()
            ->title($title)
            ->body($body)
            ->danger()
            ->send();

        $this->halt();
    }
}
