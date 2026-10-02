<?php

namespace App\Filament\Resources\RestaurantResource\Pages;

use App\Filament\Resources\RestaurantResource;
use App\Models\Restaurant;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRestaurant extends EditRecord
{
    protected static string $resource = RestaurantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('website')
                ->label('Open website')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (Restaurant $record) => RestaurantResource::getWebsiteUrl($record), shouldOpenInNewTab: true)
                ->hidden(fn (Restaurant $record) => $record->trashed()),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
