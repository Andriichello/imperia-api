<?php

namespace App\Jobs\Notification;

use App\Enums\NotificationChannel;
use App\Jobs\AsyncJob;
use App\Models\Notification;
use Exception;

/**
 * Class DispatchNotifications.
 */
class DispatchNotifications extends AsyncJob
{
    /**
     * Max number of notifications to be queued.
     *
     * @var int
     */
    protected int $limit;

    /**
     * DispatchNotifications constructor.
     *
     * @param int $limit
     */
    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        Notification::query()
            ->sendNow()
            ->limit($this->limit)
            ->eachById(function (Notification $notification) {
                dispatch(new SendNotification($notification));
            });
    }
}
