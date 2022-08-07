<?php

namespace App\Subscribers;

use App\Enums\BanquetState;
use App\Enums\NotificationChannel;
use App\Models\Banquet;
use App\Models\Notification;

/**
 * Class BanquetSubscriber.
 */
class BanquetSubscriber extends BaseSubscriber
{
    protected function map(): void
    {
        $this->map = [
            Banquet::eloquentEvent('updating') => 'updating',
        ];
    }

    public function updating(Banquet $banquet)
    {
        if ($banquet->isClean('state')) {
            return;
        }

        $notification = new Notification();

        $notification->receiver_id = $banquet->customer->user_id;
        $notification->channel = NotificationChannel::Default;
        $notification->send_at = now();

        switch ($banquet->state) {
            case BanquetState::New:
                $notification->subject = 'Order is being accepted.';
                $notification->body = "Your order (id: {$banquet->order->id})"
                    . " for banquet (id: {$banquet->id}) is being accepted.";
                break;

            case BanquetState::Cancelled:
                $notification->subject = 'Order is being cancelled.';
                $notification->body = "Your order (id: {$banquet->order->id})"
                    . " for banquet (id: {$banquet->id}) is being cancelled.";
                break;

            case BanquetState::Processing:
                $notification->subject = 'Order is being processed.';
                $notification->body = "Your order (id: {$banquet->order->id})"
                    . " for banquet (id: {$banquet->id}) is being processed.";
                break;

            case BanquetState::Completed:
                $notification->subject = 'Order is being completed.';
                $notification->body = "Your order (id: {$banquet->order->id})"
                    . " for banquet (id: {$banquet->id}) is being completed.";
                break;

            default:
                return;
        }

        $notification->save();
    }
}
