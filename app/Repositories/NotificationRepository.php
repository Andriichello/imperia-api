<?php

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NotificationRepository.
 */
class NotificationRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Notification::class;
}
