<?php

namespace App\Http\Requests\Notification;

use App\Enums\NotificationChannel;
use App\Http\Requests\Crud\ShowRequest;
use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Notification;

/**
 * Class UpdateNotificationRequest.
 */
class UpdateNotificationRequest extends UpdateRequest
{
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
                'subject' => [
                    'sometimes',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'body' => [
                    'sometimes',
                    'string',
                    'min:1',
                ],
                'payload' => [
                    'sometimes',
                    'object',
                ],
                'send_at' => [
                    'sometimes',
                    'date',
                    'after_or_equal:today',
                ],
            ]
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var Notification $notification */
        $notification = Notification::query()
            ->findOrFail($this->id());

        return !$notification->sent_at && $notification->sender_id === $this->userId();
    }

    /**
     * @OA\Schema(
     *   schema="UpdateNotificationRequest",
     *   description="Update notification request",
     *   @OA\Property(property="subject", type="string", example="Notification subject."),
     *   @OA\Property(property="body", type="string", example="Notification body..."),
     *   @OA\Property(property="payload", type="object", nullable="true", example="{}"),
     *   @OA\Property(property="send_at", type="string", format="date-time", example="2022-12-12 10:00:00",
     *     description="Date and time when notification should be sent. Only future values are acceptable."),
     *  )
     */
}
