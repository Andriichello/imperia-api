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
     * Only notifications that given user send.
     *
     * @param User|int $user
     *
     * @return static
     */
    public function fromUser(User|int $user): static
    {
        $userId = is_int($user) ? $user : $user->id;
        $this->where('sender_id', $userId);

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
     * Only notifications that given user receive.
     *
     * @param User|int $user
     *
     * @return static
     */
    public function toUser(User|int $user): static
    {
        $userId = is_int($user) ? $user : $user->id;
        $this->where('receiver_id', $userId);

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
     * Only notifications that should be sent next.
     *
     * @return $this
     */
    public function nextToBeSent(): static
    {
        $this->wasNotSent()
            ->where('send_at', '<=', now());

        return $this;
    }
}
