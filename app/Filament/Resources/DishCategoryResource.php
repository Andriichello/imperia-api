<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\Resources\DishCategoryResource\Pages;
use App\Models\DishCategory;
use App\Models\DishMenu;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Class DishCategoryResource.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DishCategoryResource extends BaseResource
{
    protected static ?string $model = DishCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Dish Management';

    protected static ?string $modelLabel = 'Category';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('menu_id')
                    ->label('Menu')
                    ->options(DishMenu::all()->pluck('title', 'id'))
                    ->required()
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('menu.title')
                    ->label('Menu')
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
            'index' => Pages\ListDishCategories::route('/'),
            'create' => Pages\CreateDishCategory::route('/create'),
            'edit' => Pages\EditDishCategory::route('/{record}/edit'),
        ];
    }
}
