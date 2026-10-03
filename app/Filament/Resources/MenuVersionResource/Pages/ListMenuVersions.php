<?php

namespace App\Filament\Resources\MenuVersionResource\Pages;

use App\Filament\Resources\MenuVersionResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Class ListMenuVersions.
 */
class ListMenuVersions extends ListRecords
{
    /**
     * The resource of the page.
     *
     * @var string
     */
    protected static string $resource = MenuVersionResource::class;
}
