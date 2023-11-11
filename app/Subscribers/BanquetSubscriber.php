<?php

namespace App\Subscribers;

use App\Enums\BanquetState;
use App\Enums\NotificationChannel;
use App\Jobs\Order\CalculateTotals;
use App\Models\Banquet;
use App\Models\Notification;
use App\Models\Orders\BanquetOrder;
use App\Models\Orders\Order;

/**
 * Class BanquetSubscriber.
 */
class BanquetSubscriber extends BaseSubscriber
{
    protected function map(): void
    {
        $this->map = [
            Banquet::eloquentEvent('updating') => 'updating',
            BanquetOrder::eloquentEvent('created') => 'orderAttached',
            BanquetOrder::eloquentEvent('deleted') => 'orderDetached',
        ];
    }

    public function updating(Banquet $banquet)
    {
        if ($banquet->isClean('state')) {
            return;
        }

        if (!$banquet->customer_id || !$banquet->customer->user_id) {
            return;
        }

        $notification = new Notification();

        $notification->receiver_id = $banquet->customer->user_id;
        $notification->channel = NotificationChannel::Default;
        $notification->send_at = now();
        $notification->payload = [
            'type' => 'banquet.state.change',
            'banquet_id' => $banquet->id,
            'new_state' => $banquet->state,
            'old_state' => $banquet->getOriginal('state'),
        ];

        switch ($banquet->state) {
            case BanquetState::New:
                $notification->subject = 'Order is being created.';
                $notification->body = "Your order for banquet #{$banquet->id} is being created.";
                break;

            case BanquetState::Postponed:
                $notification->subject = 'Order is being postponed.';
                $notification->body = "Your order for banquet #{$banquet->id} is being postponed.";
                break;

            case BanquetState::Cancelled:
                $notification->subject = 'Order is being cancelled.';
                $notification->body = "Your order for banquet #{$banquet->id} is being cancelled.";
                break;

            case BanquetState::Completed:
                $notification->subject = 'Order is being completed.';
                $notification->body = "Your order for banquet #{$banquet->id} is being completed.";
                break;

            default:
                return;
        }

        $notification->save();
    }

    public function orderAttached(BanquetOrder $pivot)
    {
        if (empty($pivot->order)) {
            return;
        }

        CalculateTotals::dispatchSync($pivot->order);
    }

    public function orderDetached(BanquetOrder $pivot)
    {
        if (empty($pivot->banquet) || $pivot->banquet->totals === null) {
            return;
        }

        $pivot->banquet->totals = null;
        $pivot->banquet->save();
    }
}
