<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class NotificationCollection.
 */
class NotificationCollection extends ResourceCollection
{
    public $collects = NotificationResource::class;
}
