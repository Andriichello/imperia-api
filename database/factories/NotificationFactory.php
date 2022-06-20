<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Models\Notification;
use App\Models\User;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class NotificationFactory.
 *
 * @method Notification|Collection create($attributes = [], ?Model $parent = null)
 */
class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $data = [
            'subject' => $this->faker->sentence(2),
            'body' => $this->faker->text(),
            'payload' => [
                'id' => 1,
                'amount' => 3,
            ]
        ];

        return [
            'data' => json_encode($data),
            'channel' => 'default',
            'send_at' => now(),
        ];
    }

    /**
     * Indicate notification's channel.
     *
     * @param string $channel
     *
     * @return static
     */
    public function withChannel(string $channel = NotificationChannel::Default): static
    {
        return $this->state(
            function (array $attributes) use ($channel) {
                $attributes['channel'] = $channel;
                return $attributes;
            }
        );
    }

    /**
     * Indicate time, when notification should be sent.
     *
     * @param CarbonInterface $sendAt
     *
     * @return static
     */
    public function withSendAt(CarbonInterface $sendAt): static
    {
        return $this->state(
            function (array $attributes) use ($sendAt) {
                $attributes['send_at'] = $sendAt;
                return $attributes;
            }
        );
    }

    /**
     * Indicate time, when notification was sent.
     *
     * @param ?CarbonInterface $sentAt
     *
     * @return static
     */
    public function withSentAt(?CarbonInterface $sentAt): static
    {
        return $this->state(
            function (array $attributes) use ($sentAt) {
                $attributes['sent_at'] = $sentAt;
                return $attributes;
            }
        );
    }

    /**
     * Indicate time, when notification was seen.
     *
     * @param ?CarbonInterface $seenAt
     *
     * @return static
     */
    public function withSeenAt(?CarbonInterface $seenAt): static
    {
        return $this->state(
            function (array $attributes) use ($seenAt) {
                $attributes['seen_at'] = $seenAt;
                return $attributes;
            }
        );
    }

    /**
     * Indicate user, which sends the notification.
     *
     * @param User $sender
     *
     * @return static
     */
    public function withSender(User $sender): static
    {
        return $this->state(
            function (array $attributes) use ($sender) {
                $attributes['sender_id'] = $sender->id;
                return $attributes;
            }
        );
    }

    /**
     * Indicate user, which receives the notification.
     *
     * @param User $receiver
     *
     * @return static
     */
    public function withReceiver(User $receiver): static
    {
        return $this->state(
            function (array $attributes) use ($receiver) {
                $attributes['receiver_id'] = $receiver->id;
                return $attributes;
            }
        );
    }
}
