<?php

namespace App\Http\Resources\Alternation;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class AlternationCollection.
 */
class AlternationCollection extends ResourceCollection
{
    public $collects = AlternationResource::class;
}
