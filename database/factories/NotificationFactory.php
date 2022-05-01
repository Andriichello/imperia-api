<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
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
        return [
            'data' => [
                'subject' => $this->faker->sentence(2),
                'body' => $this->faker->text(),
                'payload' => [
                    'id' => 1,
                    'amount' => 3,
                ]
            ],
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
    public function withChannel(string $channel = 'default'): static
    {
        return $this->state(
            function (array $attributes) use ($channel) {
                $attributes['channel'] = $channel;
                return $attributes;
            }
        );
    }

    /**
     * Indicate notification's channel.
     *
     * @param DateTimeInterface $sendAt
     *
     * @return static
     */
    public function withSendAt(DateTimeInterface $sendAt): static
    {
        return $this->state(
            function (array $attributes) use ($sendAt) {
                $attributes['send_at'] = $sendAt;
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
