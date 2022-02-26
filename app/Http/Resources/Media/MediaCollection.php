<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class MediaCollection.
 */
class MediaCollection extends ResourceCollection
{
    public $collects = MediaResource::class;
}
