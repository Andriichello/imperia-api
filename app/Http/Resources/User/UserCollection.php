<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class UserCollection.
 */
class UserCollection extends ResourceCollection
{
    public $collects = UserResource::class;
}
