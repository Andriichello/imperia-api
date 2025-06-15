<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\Fields\RestaurantSelect;
use App\Filament\Resources\DishMenuResource\Pages;
use App\Models\DishMenu;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Class DishMenuResource.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DishMenuResource extends BaseResource
{
    protected static ?string $model = DishMenu::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Dish Management';

    protected static ?string $modelLabel = 'Menu';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                RestaurantSelect::make()
                    ->required(),
                TextInput::make('slug')
                    ->maxLength(255),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->maxLength(1020)
                    ->columnSpanFull(),
                Toggle::make('archived')
                    ->default(false),
                TextInput::make('popularity')
                    ->numeric()
                    ->nullable(),
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
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
            'index' => Pages\ListDishMenus::route('/'),
            'create' => Pages\CreateDishMenu::route('/create'),
            'edit' => Pages\EditDishMenu::route('/{record}/edit'),
        ];
    }
}
