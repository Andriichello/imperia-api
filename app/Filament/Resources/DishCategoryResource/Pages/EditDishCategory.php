<?php

namespace App\Filament\Resources\DishCategoryResource\Pages;

use App\Filament\Resources\DishCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDishCategory extends EditRecord
{
    protected static string $resource = DishCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
