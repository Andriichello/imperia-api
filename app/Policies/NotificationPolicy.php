<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NotificationPolicy.
 */
class NotificationPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Notification::class;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function view(User $user, Notification $notification): bool
    {
        return $this->isSender($user, $notification)
            || ($notification->sent_at && $this->isReceiver($user, $notification));
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function update(User $user, Notification $notification): bool
    {
        return !$notification->sent_at && $this->isSender($user, $notification);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function delete(User $user, Notification $notification): bool
    {
        return !$notification->sent_at && $this->isSender($user, $notification);
    }

    /**
     * Determine if user is a sender of given notification.
     *
     * @param User $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function isSender(User $user, Notification $notification): bool
    {
        return $notification->sender_id === $user->id;
    }

    /**
     * Determine if user is a receiver of given notification.
     *
     * @param User $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function isReceiver(User $user, Notification $notification): bool
    {
        return $notification->receiver_id === $user->id;
    }
}
