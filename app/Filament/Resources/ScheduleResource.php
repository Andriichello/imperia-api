<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\Fields\RestaurantSelect;
use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Class ScheduleResource.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScheduleResource extends BaseResource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                RestaurantSelect::make()
                    ->required(),
                Select::make('weekday')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    ])
                    ->required(),
                TextInput::make('beg_hour')
                    ->label('Beginning Hour')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(23)
                    ->required(),
                TextInput::make('beg_minute')
                    ->label('Beginning Minute')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(59)
                    ->required(),
                TextInput::make('end_hour')
                    ->label('Ending Hour')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(23)
                    ->required(),
                TextInput::make('end_minute')
                    ->label('Ending Minute')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(59)
                    ->required(),
                Toggle::make('archived')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('restaurant.name')
                    ->label('Restaurant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('weekday')
                    ->searchable(),
                Tables\Columns\TextColumn::make('beg_hour')
                    ->label('Begin Hour'),
                Tables\Columns\TextColumn::make('beg_minute')
                    ->label('Begin Minute'),
                Tables\Columns\TextColumn::make('end_hour')
                    ->label('End Hour'),
                Tables\Columns\TextColumn::make('end_minute')
                    ->label('End Minute'),
                Tables\Columns\IconColumn::make('archived')
                    ->label('Live')
                    ->alignCenter()
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
