<?php

namespace App\Http\Resources\Banquet;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class CommentCollection.
 */
class BanquetCollection extends ResourceCollection
{
    public $collects = BanquetResource::class;
}
