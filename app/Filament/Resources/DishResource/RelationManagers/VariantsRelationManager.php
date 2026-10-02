<?php

namespace App\Filament\Resources\DishResource\RelationManagers;

use App\Filament\Filters\TrashedFilter;
use App\Filament\Resources\DishVariantResource;
use App\Filament\Tables\AlterationsTable;
use App\Filament\Tables\Columns\LiveColumn;
use App\Models\Dish;
use App\Models\DishVariant;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class VariantsRelationManager.
 *
 * Variants (e.g. sizes) of a dish, shown and edited on the dish's edit page.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VariantsRelationManager extends RelationManager
{
    /**
     * @var string
     */
    protected static string $relationship = 'allVariants';

    /**
     * @var string|null
     */
    protected static ?string $title = 'Variants';

    /**
     * @var string|null
     */
    protected static ?string $modelLabel = 'variant';

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
            ->schema(DishVariantResource::getAlterableFields());
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
            ->modifyQueryUsing(fn (Builder $query) => AlterationsTable::withScheduledChangesCount($query))
            ->recordTitle(fn (DishVariant $record) => static::getVariantLabel($record))
            ->columns([
                Tables\Columns\TextColumn::make('price')
                    ->money(fn () => $this->getCurrency()),
                Tables\Columns\TextColumn::make('weight')
                    ->state(fn (DishVariant $record) => static::getVariantLabel($record, null))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('calories')
                    ->numeric()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('preparation_time')
                    ->label('Prep Time (min)')
                    ->numeric()
                    ->placeholder('—'),
                LiveColumn::make(),
                AlterationsTable::scheduledColumn(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->emptyStateHeading('No variants')
            ->emptyStateDescription('Add variants when the dish comes in several sizes, e.g. 300 g and 500 g.')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('schedule')
                    ->label('Schedule')
                    ->tooltip('Open the variant to schedule changes')
                    ->icon('heroicon-o-clock')
                    ->url(fn (DishVariant $record) => DishVariantResource::getUrl('edit', ['record' => $record]))
                    ->hidden(fn (DishVariant $record) => $record->trashed()),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Label of a variant: its weight with the unit (e.g. "300 g").
     *
     * @param DishVariant $variant
     * @param string|null $default
     *
     * @return string|null
     */
    public static function getVariantLabel(DishVariant $variant, ?string $default = 'variant'): ?string
    {
        return trim($variant->weight . ' ' . $variant->weight_unit) ?: $default;
    }

    /**
     * Currency of the dish's restaurant.
     *
     * @return string
     */
    protected function getCurrency(): string
    {
        /** @var Dish $dish */
        $dish = $this->getOwnerRecord();

        return $dish->menu->restaurant->currency ?: 'UAH';
    }
}
