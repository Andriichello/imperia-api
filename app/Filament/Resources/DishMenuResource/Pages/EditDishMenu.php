<?php

namespace App\Filament\Resources\DishMenuResource\Pages;

use App\Filament\Resources\DishMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDishMenu extends EditRecord
{
    protected static string $resource = DishMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
