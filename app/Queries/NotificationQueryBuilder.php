<?php

namespace App\Queries;

use App\Models\Orders\Order;
use App\Models\Space;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class NotificationQueryBuilder.
 */
class NotificationQueryBuilder extends EloquentBuilder
{
    /**
     * Extract ids from given users.
     *
     * @param User|int ...$users
     *
     * @return array
     */
    protected function extractUserIds(User|int ...$users): array
    {
        $closure = fn(User|int $user) => is_int($user) ? $user : $user->id;

        return array_map($closure, $users);
    }

    /**
     * Only notifications that given user send.
     *
     * @param User|int ...$users
     *
     * @return static
     */
    public function fromUsers(User|int ...$users): static
    {
        $this->whereIn('sender_id', $this->extractUserIds(...$users));

        return $this;
    }

    /**
     * Only notifications that system send.
     *
     * @return $this
     */
    public function fromSystem(): static
    {
        $this->whereNull('sender_id');

        return $this;
    }

    /**
     * Only notifications that is not from system.
     *
     * @return $this
     */
    public function notFromSystem(): static
    {
        $this->whereNotNull('sender_id');

        return $this;
    }

    /**
     * Only notifications that given users receive.
     *
     * @param User|int ...$users
     *
     * @return static
     */
    public function toUsers(User|int ...$users): static
    {
        $this->whereIn('receiver_id', $this->extractUserIds(...$users));

        return $this;
    }

    /**
     * Only notifications that was already sent.
     *
     * @return $this
     */
    public function wasSent(): static
    {
        $this->whereNotNull('sent_at');

        return $this;
    }

    /**
     * Only notifications that wasn't sent.
     *
     * @return $this
     */
    public function wasNotSent(): static
    {
        $this->whereNull('sent_at');

        return $this;
    }

    /**
     * Only notifications that should already be sent.
     *
     * @return $this
     */
    public function sendNow(): static
    {
        $this->wasNotSent()
            ->where('send_at', '<=', now());

        return $this;
    }

    /**
     * Only notifications that should be sent later.
     *
     * @return $this
     */
    public function sendLater(): static
    {
        $this->wasNotSent()
            ->where('send_at', '>', now());

        return $this;
    }

    /**
     * Only notifications that was seen.
     *
     * @return $this
     */
    public function wasSeen(): static
    {
        $this->whereNotNull('seen_at');

        return $this;
    }

    /**
     * Only notifications that wasn't seen.
     *
     * @return $this
     */
    public function wasNotSeen(): static
    {
        $this->whereNull('seen_at');

        return $this;
    }

    /**
     * Only notifications in given channels.
     *
     * @param string ...$channels
     *
     * @return $this
     */
    public function inChannels(string ...$channels): static
    {
        if (empty($channels) === false) {
            $this->whereIn('channel', $channels);
        }

        return $this;
    }
}
