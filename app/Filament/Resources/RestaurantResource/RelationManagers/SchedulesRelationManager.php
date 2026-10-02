<?php

namespace App\Filament\Resources\RestaurantResource\RelationManagers;

use App\Enums\Weekday;
use App\Filament\Tables\Columns\LiveColumn;
use App\Models\Schedule;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

/**
 * Class SchedulesRelationManager.
 *
 * Opening hours of a restaurant (one row per weekday), shown on its edit page.
 * The form edits times, which are stored as separate hour and minute columns.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SchedulesRelationManager extends RelationManager
{
    /**
     * @var string
     */
    protected static string $relationship = 'schedules';

    /**
     * @var string|null
     */
    protected static ?string $title = 'Opening hours';

    /**
     * @var string|null
     */
    protected static ?string $modelLabel = 'opening hours';

    /**
     * @var string|null
     */
    protected static ?string $pluralModelLabel = 'opening hours';

    /**
     * Always check abilities through the policies (see `BaseResource`).
     *
     * @return bool
     */
    public static function shouldCheckPolicyExistence(): bool
    {
        return false;
    }

    /**
     * Configure the form.
     *
     * @param Form $form
     *
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('weekday')
                    ->label('Day')
                    ->options(static::getWeekdayLabels())
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule) => $rule
                            ->where('restaurant_id', $this->getOwnerRecord()->getKey()),
                    )
                    ->validationMessages(['unique' => 'This day already has opening hours.'])
                    ->required()
                    ->columnSpanFull(),
                ...static::getTimeFields(),
                Toggle::make('archived')
                    ->label('Closed on this day')
                    ->helperText('The hours are kept, but the website shows the restaurant as closed.')
                    ->default(false)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Configure the table.
     *
     * @param Table $table
     *
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            // the weekday column is an enum, which sorts from Monday to Sunday
            ->defaultSort('weekday')
            ->recordTitle(fn (Schedule $record) => static::getWeekdayLabels()[$record->weekday] ?? $record->weekday)
            ->columns([
                Tables\Columns\TextColumn::make('weekday')
                    ->label('Day')
                    ->formatStateUsing(fn (string $state) => static::getWeekdayLabels()[$state] ?? $state),
                Tables\Columns\TextColumn::make('opens')
                    ->state(fn (Schedule $record) => static::formatTime($record->beg_hour, $record->beg_minute)),
                Tables\Columns\TextColumn::make('closes')
                    ->state(fn (Schedule $record) => static::formatTime($record->end_hour, $record->end_minute)
                        . ($record->is_cross_date ? ' (next day)' : '')),
                LiveColumn::make('archived')
                    ->label('Open'),
            ])
            ->paginated(false)
            ->emptyStateHeading('No opening hours')
            ->emptyStateDescription('The website shows the restaurant as closed. '
                . 'Use "Set weekly hours" to fill several days at once.')
            ->headerActions([
                $this->weeklyHoursAction(),
                Tables\Actions\CreateAction::make()
                    ->label('Add day')
                    ->mutateFormDataUsing(fn (array $data) => static::toColumns($data)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data) => static::toTimes($data))
                    ->mutateFormDataUsing(fn (array $data) => static::toColumns($data)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Action, which sets the same hours for the selected days (adds or replaces them).
     *
     * @return Tables\Actions\Action
     */
    protected function weeklyHoursAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('weeklyHours')
            ->label('Set weekly hours')
            ->icon('heroicon-o-calendar-days')
            ->modalHeading('Set weekly hours')
            ->modalDescription('The hours of the selected days are replaced, other days stay as they are.')
            ->visible(fn () => Gate::allows('create', Schedule::class))
            ->form([
                CheckboxList::make('weekdays')
                    ->label('Days')
                    ->options(static::getWeekdayLabels())
                    ->default(Weekday::getValues())
                    ->columns(4)
                    ->required(),
                ...static::getTimeFields(),
            ])
            ->action(function (array $data) {
                $values = static::toColumns($data);

                foreach ($data['weekdays'] as $weekday) {
                    Schedule::query()->updateOrCreate(
                        ['restaurant_id' => $this->getOwnerRecord()->getKey(), 'weekday' => $weekday],
                        [...$values, 'archived' => false],
                    );
                }

                Notification::make()
                    ->title('The opening hours were saved')
                    ->success()
                    ->send();
            });
    }

    /**
     * Opening and closing time inputs.
     *
     * @return TimePicker[]
     */
    protected static function getTimeFields(): array
    {
        return [
            TimePicker::make('opens_at')
                ->label('Opens')
                ->seconds(false)
                ->required(),
            TimePicker::make('closes_at')
                ->label('Closes')
                ->helperText('If it closes after midnight, enter that time (e.g. 02:00).')
                ->seconds(false)
                ->required(),
        ];
    }

    /**
     * Weekdays from Monday to Sunday with their labels.
     *
     * @return array<string, string>
     */
    protected static function getWeekdayLabels(): array
    {
        $labels = [];

        foreach (Weekday::getValues() as $weekday) {
            $labels[$weekday] = Str::ucfirst($weekday);
        }

        return $labels;
    }

    /**
     * Replace the times of the form with the hour and minute columns.
     *
     * @param array $data
     *
     * @return array
     */
    protected static function toColumns(array $data): array
    {
        [$data['beg_hour'], $data['beg_minute']] = static::parseTime($data['opens_at']);
        [$data['end_hour'], $data['end_minute']] = static::parseTime($data['closes_at']);

        unset($data['opens_at'], $data['closes_at'], $data['weekdays']);

        return $data;
    }

    /**
     * Add the times for the form from the hour and minute columns.
     *
     * @param array $data
     *
     * @return array
     */
    protected static function toTimes(array $data): array
    {
        $data['opens_at'] = static::formatTime($data['beg_hour'], $data['beg_minute']);
        $data['closes_at'] = static::formatTime($data['end_hour'], $data['end_minute']);

        return $data;
    }

    /**
     * Hour and minute of a time like "09:30" or "09:30:00".
     *
     * @param string $time
     *
     * @return int[]
     */
    protected static function parseTime(string $time): array
    {
        [$hour, $minute] = explode(':', $time) + [1 => 0];

        return [(int) $hour, (int) $minute];
    }

    /**
     * Time like "09:30".
     *
     * @param int|string|null $hour
     * @param int|string|null $minute
     *
     * @return string
     */
    protected static function formatTime(int|string|null $hour, int|string|null $minute): string
    {
        return sprintf('%02d:%02d', (int) $hour, (int) $minute);
    }
}
