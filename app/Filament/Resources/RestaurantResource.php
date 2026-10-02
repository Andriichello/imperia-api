<?php

namespace App\Filament\Resources;

use App\Enums\Currency;
use App\Enums\Establishment;
use App\Filament\BaseResource;
use App\Filament\Filters\TrashedFilter;
use App\Filament\Forms\Components\MediaAttachmentField;
use App\Filament\Resources\RestaurantResource\Pages;
use App\Filament\Resources\RestaurantResource\RelationManagers\SchedulesRelationManager;
use App\Models\Restaurant;
use DateTimeZone;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Locale;

/**
 * Class RestaurantResource.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RestaurantResource extends BaseResource
{
    protected static ?string $model = Restaurant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Restaurant')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('establishment')
                            ->label('Type')
                            ->helperText('The website uses it in its wording, e.g. "Café" instead of "Restaurant".')
                            ->options(fn (?string $state) => static::withCurrentOption(
                                Establishment::getLabels(),
                                $state
                            ))
                            ->in(fn (?Restaurant $record) => array_keys(static::withCurrentOption(
                                Establishment::getLabels(),
                                $record?->establishment
                            ))),
                        TextInput::make('popularity')
                            ->numeric()
                            ->nullable(),
                    ])
                    ->columns(2),
                Section::make('Address and contacts')
                    ->schema([
                        TextInput::make('place')
                            ->label('Address')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('city')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('country')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('location')
                            ->label('Map link')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Region')
                    ->schema([
                        Select::make('timezone')
                            ->searchable()
                            ->options(array_combine(
                                DateTimeZone::listIdentifiers(),
                                DateTimeZone::listIdentifiers()
                            ))
                            ->default('Europe/Kyiv')
                            ->required(),
                        Select::make('currency')
                            ->options(fn (?string $state) => static::withCurrentOption(Currency::getLabels(), $state))
                            ->in(fn (?Restaurant $record) => array_keys(static::withCurrentOption(
                                Currency::getLabels(),
                                static::normalizeCurrency($record?->currency)
                            )))
                            // older restaurants may have lowercase codes
                            ->afterStateHydrated(fn (Select $component, ?string $state) => $component
                                ->state(static::normalizeCurrency($state)))
                            ->default(Currency::Hryvnia)
                            ->required(),
                        Select::make('locale')
                            ->label('Language')
                            ->helperText('Used by the website, when the link has no language.')
                            ->options(fn (?string $state) => static::withCurrentOption(
                                static::getLocaleLabels(),
                                $state
                            ))
                            ->in(fn (?Restaurant $record) => array_keys(static::withCurrentOption(
                                static::getLocaleLabels(),
                                $record?->locale
                            ))),
                    ])
                    ->columns(3),
                Repeater::make('notes')
                    ->label('Notes')
                    ->helperText('Short notes shown under the restaurant\'s name, e.g. "Pets welcome". '
                        . 'Hidden ones stay here, but guests don\'t see them.')
                    ->relationship()
                    ->orderColumn('order')
                    ->schema([
                        TextInput::make('text')
                            ->hiddenLabel()
                            ->required()
                            ->maxLength(120)
                            ->columnSpan(5),
                        Toggle::make('is_hidden')
                            ->label('Hidden')
                            ->inline(false)
                            ->default(false),
                    ])
                    ->columns(6)
                    ->defaultItems(0)
                    ->addActionLabel('Add note')
                    ->columnSpanFull(),
                MediaAttachmentField::make('media')
                    ->label('Restaurant images')
                    ->modelType('restaurants')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxFiles(5)
                    ->maxSize(2048)
                    ->preview(true)
                    ->multiple(true)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(query: fn (Builder $query, string $search) => static::searchTranslated(
                        $query,
                        'restaurants.name',
                        $search
                    )),
                Tables\Columns\TextColumn::make('establishment')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state) => Establishment::getLabels()[$state] ?? $state),
                Tables\Columns\TextColumn::make('country')->searchable(),
                Tables\Columns\TextColumn::make('city')->searchable(),
                Tables\Columns\TextColumn::make('currency'),
                Tables\Columns\TextColumn::make('timezone'),
                Tables\Columns\TextColumn::make('popularity')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('website')
                    ->label('Open website')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Restaurant $record) => static::getWebsiteUrl($record), shouldOpenInNewTab: true)
                    ->hidden(fn (Restaurant $record) => $record->trashed()),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SchedulesRelationManager::class,
        ];
    }

    /**
     * Url of the restaurant's page on the website, in the restaurant's language.
     *
     * @param Restaurant $restaurant
     *
     * @return string
     */
    public static function getWebsiteUrl(Restaurant $restaurant): string
    {
        $locale = in_array($restaurant->locale, config('app.supported_locales'))
            ? $restaurant->locale
            : config('app.locale');

        return route('web.restaurant.preview', ['locale' => $locale, 'restaurant_id' => $restaurant->id]);
    }

    /**
     * Languages of the website with their labels.
     *
     * @return array<string, string>
     */
    protected static function getLocaleLabels(): array
    {
        $labels = [];

        foreach (config('app.supported_locales') as $locale) {
            $labels[$locale] = Locale::getDisplayLanguage($locale, 'en');
        }

        return $labels;
    }

    /**
     * Options with the current value added, when it isn't one of them
     * (values entered before the field became a select).
     *
     * @param array<string, string> $options
     * @param string|null $current
     *
     * @return array<string, string>
     */
    protected static function withCurrentOption(array $options, ?string $current): array
    {
        if (filled($current) && !array_key_exists($current, $options)) {
            $options[$current] = $current;
        }

        return $options;
    }

    /**
     * Currency code in upper case.
     *
     * @param string|null $currency
     *
     * @return string|null
     */
    protected static function normalizeCurrency(?string $currency): ?string
    {
        return filled($currency) ? strtoupper($currency) : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRestaurants::route('/'),
            'create' => Pages\CreateRestaurant::route('/create'),
            'edit' => Pages\EditRestaurant::route('/{record}/edit'),
        ];
    }
}
