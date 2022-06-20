<?php

namespace App\Queries;

use App\Enums\NotificationChannel;
use App\Models\User;

/**
 * Class NotificationQueryBuilder.
 */
class NotificationQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(User $user): static
    {
        $this->inChannels(NotificationChannel::Default)
            ->forUser($user);

        return $this;
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
        $this->whereIn('sender_id', $this->extract('id', ...$users));

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
        $this->whereIn('receiver_id', $this->extract('id', ...$users));

        return $this;
    }

    /**
     * Only notifications that are available for given user.
     *
     * @param User|int $user
     *
     * @return static
     */
    public function forUser(User|int $user): static
    {
        $closure = function (NotificationQueryBuilder $builder) use ($user) {
            $builder->fromUsers($user)
                ->orWhereWrapped(
                    function (NotificationQueryBuilder $builder) use ($user) {
                        $builder->toUsers($user)->wasSent();
                    }
                );
        };

        return $this->whereWrapped($closure);
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
