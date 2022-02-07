<?php

namespace App\Http\Resources\Menu;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class MenuCollection.
 */
class MenuCollection extends ResourceCollection
{
    public $collects = MenuResource::class;
}
