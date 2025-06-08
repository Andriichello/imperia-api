<?php

namespace App\Filament\Fields;

use App\Filament\Resources\RestaurantResource;
use Filament\Forms\Components\Select;

/**
 * Class RestaurantId.
 */
class RestaurantId extends Select
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

        $this->label('Restaurant');

        $options = RestaurantResource::getEloquentQuery()
            ->get()
            ->pluck('name', 'id');

        $this->options($options);
    }
}
