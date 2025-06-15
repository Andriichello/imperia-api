<?php

namespace App\Filament\Resources\DishVariantResource\Pages;

use App\Filament\Resources\DishVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDishVariant extends EditRecord
{
    protected static string $resource = DishVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
