<?php

namespace App\Http\Requests\Notification;

use App\Enums\NotificationChannel;
use App\Http\Requests\Crud\ShowRequest;

/**
 * Class StoreNotificationRequest.
 */
class StoreNotificationRequest extends ShowRequest
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
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'body' => [
                    'required',
                    'string',
                    'min:1',
                ],
                'payload' => [
                    'nullable',
                    'object',
                ],
                'send_at' => [
                    'required',
                    'date',
                    'after_or_equal:today',
                ],
                'channel' => [
                    'required',
                    'string',
                    'in:' . NotificationChannel::Default,
                ],
                'receiver_id' => [
                    'required',
                    'integer',
                    'not_in:' . $this->userId(),
                    'exists:users,id',
                ],
            ]
        );
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'send_at' => now(),
            'sender_id' => $this->userId(),
            'channel' => NotificationChannel::Default,
        ]);
    }

    /**
     * @OA\Schema(
     *   schema="StoreNotificationRequest",
     *   description="Store notification request",
     *   required = {"subject", "body", "payload", "receiver_id"},
     *   @OA\Property(property="subject", type="string", example="Notification subject."),
     *   @OA\Property(property="body", type="string", example="Notification body..."),
     *   @OA\Property(property="payload", type="object", nullable="true", example="{}"),
     *   @OA\Property(property="receiver_id", type="integer", example=1),
     *   @OA\Property(property="channel", type="string", example="default", enum={"default"}),
     *   @OA\Property(property="send_at", type="string", format="date-time", example="2022-12-12 10:00:00",
     *     description="Date and time when notification should be sent. Only future values are acceptable."),
     *  )
     */
}
