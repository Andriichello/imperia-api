<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\Crud\ShowRequest;
use App\Models\Notification;

/**
 * Class ShowNotificationRequest.
 */
class ShowNotificationRequest extends ShowRequest
{
    /**
     * @var Notification
     */
    protected Notification $notification;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                //
            ]
        );
    }

    /**
     * @return Notification
     */
    public function getNotification(): Notification
    {
        if (isset($this->notification)) {
            return $this->notification;
        }

        /** @var Notification $notification */
        $notification = Notification::query()
            ->findOrFail($this->id());

        return $this->notification = $notification;
    }

    public function isBySender(): bool
    {
        return $this->getNotification()->sender_id === $this->userId();
    }

    public function isByReceiver(): bool
    {
        $notification = $this->getNotification();
        return $notification->sent_at
            && $notification->receiver_id === $this->userId();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->isBySender() || $this->isByReceiver();
    }
}
