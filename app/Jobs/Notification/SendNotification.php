<?php

namespace App\Jobs\Notification;

use App\Enums\NotificationChannel;
use App\Jobs\AsyncJob;
use App\Models\Notification;
use Exception;

/**
 * Class SendNotification.
 */
class SendNotification extends AsyncJob
{
    /**
     * @var Notification
     */
    protected Notification $notification;

    /**
     * SendNotification constructor.
     *
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        if ($this->notification->channel !== NotificationChannel::Default) {
            throw new Exception(
                'Sending notifications via ' . $this->notification->channel
                . ' channel has not been implemented yet'
            );
        }

        // mark notification as sent, so it can be fetched via api
        $this->notification->sent_at = now();
        $this->notification->save();
    }
}
