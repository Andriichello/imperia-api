<?php

namespace App\Http\Resources\Notification;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MenuResource.
 *
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'channel' => $this->channel,
            'subject' => $this->subject,
            'body' => $this->body,
            'payload' => $this->payload,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Notification",
     *   description="Notification resource object",
     *   required = {"id", "type",  "sender_id", "receiver_id", "subject", "body", "payload"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="menus"),
     *   @OA\Property(property="channel", type="string", example="default"),
     *   @OA\Property(property="subject", type="string", example="Order status change"),
     *   @OA\Property(property="body", type="string", nullable="true", example="Your order was accepted"),
     *   @OA\Property(property="payload", type="object", nullable="true"),
     *   @OA\Property(property="sender_id", type="integer", nullable="true", example=1),
     *   @OA\Property(property="receiver_id", type="integer", example=1),
     * )
     */
}
