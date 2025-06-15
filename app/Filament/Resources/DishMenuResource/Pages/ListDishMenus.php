<?php

namespace App\Filament\Resources\DishMenuResource\Pages;

use App\Filament\Resources\DishMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDishMenus extends ListRecords
{
    protected static string $resource = DishMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
