<?php

namespace App\Http\Resources\Notification;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

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
     * @param Request $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        // @phpstan-ignore-next-line
        $isIndex = str_contains($request->route()->getName(), '.index');
        $isAccessible = $this->seen_at || !$isIndex;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'channel' => $this->channel,
            'subject' => $this->subject,
            'body' => $isAccessible ? $this->body : null,
            'payload' => $isAccessible ? $this->payload : null,
            'send_at' => $this->send_at,
            'sent_at' => $this->sent_at,
            'seen_at' => $this->seen_at,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="NotificationsPoll",
     *   description="Notifications poll resource object",
     *   required = {"count"},
     *   @OA\Property(property="count", type="integer", example=1),
     * )
     * @OA\Schema(
     *   schema="Notification",
     *   description="Notification resource object",
     *   required = {"id", "type", "sender_id", "receiver_id", "channel", "subject",
     *     "body", "payload", "send_at", "sent_at", "seen_at"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="notifications"),
     *   @OA\Property(property="channel", type="string", example="default"),
     *   @OA\Property(property="subject", type="string", example="Order status change"),
     *   @OA\Property(property="body", type="string", nullable=true, example="Your order was accepted"),
     *   @OA\Property(property="payload", type="object", nullable=true),
     *   @OA\Property(property="sender_id", type="integer", nullable=true, example=1),
     *   @OA\Property(property="receiver_id", type="integer", example=1),
     *   @OA\Property(property="send_at", type="string", format="date-time", example="2022-01-12 23:00:00"),
     *   @OA\Property(property="sent_at", type="string", format="date-time", nullable=true,
     *      example="2022-01-12 23:00:00"),
     *   @OA\Property(property="seen_at", type="string", format="date-time", nullable=true,
     *      example="null"),
     * )
     */
}
