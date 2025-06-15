<?php

namespace App\Filament\Resources\DishVariantResource\Pages;

use App\Filament\Resources\DishVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDishVariants extends ListRecords
{
    protected static string $resource = DishVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
