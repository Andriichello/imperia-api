<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class Registered.
 */
class Registered
{
    use SerializesModels;

    /**
     * User, which was just registered.
     *
     * @var User
     */
    protected User $user;

    /**
     * Phone number of the registered user.
     *
     * @var ?string
     */
    protected ?string $phone;

    /**
     * Registered constructor.
     *
     * @param User $user
     * @param string|null $phone
     */
    public function __construct(User $user, ?string $phone = null)
    {
        $this->user = $user;
        $this->phone = $phone;
    }

    /**
     * Get user, which was just registered.
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set user, which was just registered.
     *
     * @param User $user
     *
     * @return static
     */
    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get phone number.
     *
     * @return ?string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Set phone number.
     *
     * @param ?string $phone
     *
     * @return static
     */
    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
