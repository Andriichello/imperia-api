<?php

namespace App\Filament\Fields;

use App\Filament\Resources\RestaurantResource;
use App\Models\User;
use Filament\Forms\Components\Select;

/**
 * Class RestaurantId.
 */
class RestaurantSelect extends Select
{
    public static function make(string $name = 'restaurant_id'): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User|null $user */
        $user = auth()->user();
        $options = RestaurantResource::getEloquentQuery()
            ->get()
            ->pluck('name', 'id');

        $this->label('Restaurant')
            ->default($user?->restaurant_id)
            ->extraAttributes(['readonly' => $user?->restaurant_id])
            ->options($options)
            ->required()
            ->searchable();
    }
}
