<?php

namespace App\Rules;

use App\Enums\UserRole;
use App\Models\BaseModel;
use App\Models\User;
use App\Rules\Interfaces\ValidationRule;
use Closure;
use Illuminate\Support\Str;

/**
 * Class SameRestaurant.
 */
class SameRestaurant implements ValidationRule
{
    /**
     * Determines if error should be returned if there
     * is no user and the restaurant id is not null.
     *
     * @var bool
     */
    protected bool $failUnauthenticated = true;

    /**
     * Determines if error should be returned if the
     * restaurant id is null.
     *
     * @var bool
     */
    protected bool $failNullRestaurantId = false;

    /**
     * Determines if error should be returned if the
     * user is preview only.
     *
     * @var bool
     */
    protected bool $failPreviewOnly = false;

    /**
     * User or application to be used for checks.
     *
     * @var User|null
     */
    protected ?User $user;

    /**
     * Class of the model to use for searching records in the database.
     *
     * @var string|BaseModel
     */
    protected string|BaseModel $modelClass;

    /**
     * User roles that are allowed.
     *
     * @var UserRole[]|null
     */
    protected ?array $roles = null;

    /**
     * SameRestaurant constructor
     *
     * @return void
     */
    public function __construct(?User $user, string|BaseModel $modelClass)
    {
        $this->user = $user;
        $this->modelClass = $modelClass;
    }

    /**
     * Create a new instance of SameRestaurant validation rule.
     *
     * @param User|null $user
     * @param string|BaseModel $modelClass
     *
     * @return static
     */
    public static function make(?User $user, string|BaseModel $modelClass): static
    {
        /* @phpstan-ignore-next-line  */
        return new static($user, $modelClass);
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): bool
    {
        if ($this->user?->isAdmin() && $this->user->isPreviewOnly()) {
            return true;
        }

        /** @var BaseModel|null $model */
        $model = $this->modelClass::query()->find($value);

        $restaurantId = $model?->getRestaurantId();
        $userRestaurantId = $this->user?->getRestaurantId();

        if ($this->user === null) {
            if ($restaurantId && $this->failUnauthenticated) {
                $fail(static::notAvailableForUnauthenticated($attribute));

                return false;
            }

            return true;
        }

        if (!empty($this->roles)) {
            if (!$this->user->hasAnyRole(...$this->roles)) {
                $fail(static::notAvailableForRole($attribute, $this->roles));

                return false;
            }
        }

        if ($this->failPreviewOnly && $this->user->isPreviewOnly()) {
            $fail(static::notAvailableForPreviewOnly($attribute));

            return false;
        }

        if ($restaurantId === null && $this->failNullRestaurantId) {
            $fail(static::notAvailableWithin($attribute, $userRestaurantId));

            return false;
        }

        return true;
    }

    /**
     * Set `failUnauthenticated` flag's value.
     *
     * @param bool $should
     *
     * @return $this
     * @SuppressWarnings(PHPMD)
     */
    public function failUnauthenticated(bool $should = true): static
    {
        $this->failUnauthenticated = $should;

        return $this;
    }

    /**
     * Set `failNullRestaurantId` flag's value.
     *
     * @param bool $should
     *
     * @return $this
     * @SuppressWarnings(PHPMD)
     */
    public function failNullRestaurantId(bool $should = true): static
    {
        $this->failNullRestaurantId = $should;

        return $this;
    }

    /**
     * Set `failPreviewOnly` flag's value.
     *
     * @param bool $should
     *
     * @return $this
     * @SuppressWarnings(PHPMD)
     */
    public function failPreviewOnly(bool $should = true): static
    {
        $this->failPreviewOnly = $should;

        return $this;
    }

    /**
     * Clear `roles` restriction's value.
     *
     * @return $this
     */
    public function clearRoles(): static
    {
        $this->roles = null;

        return $this;
    }

    /**
     * Set `roles` restriction's value.
     *
     * @param UserRole|string ...$roles
     *
     * @return $this
     */
    public function onlyRoles(UserRole|string ...$roles): static
    {
        $roles = array_map(
            fn($role) => is_string($role) ? UserRole::fromValue($role) : $role,
            $roles
        );

        $this->roles = $roles;

        return $this;
    }

    /**
     * Set `roles` restriction's to only allow staff members.
     *
     * @return $this
     */
    public function onlyStaff(): static
    {
        $this->onlyRoles(UserRole::Manager, UserRole::Admin);

        return $this;
    }

    /**
     * Set `roles` restriction's to only allow admins members.
     *
     * @return $this
     */
    public function onlyAdmins(): static
    {
        $this->onlyRoles(UserRole::Admin);

        return $this;
    }

    /**
     * Return string for display of the given attribute.
     *
     * @param string $attribute
     *
     * @return string
     */
    public static function forDisplay(string $attribute): string
    {
        return Str::of($attribute)
            ->kebab()
            ->replace('-', ' ')
            ->value();
    }

    /**
     * Get the message for the not available for unauthenticated user error.
     *
     * @param string $attribute
     *
     * @return string
     */
    public static function notAvailableForUnauthenticated(string $attribute): string
    {
        $display = static::forDisplay($attribute);

        return "The given $display is not available for unauthenticated users.";
    }

    /**
     * Get the message for the not available within your restaurant error.
     *
     * @param string $attribute
     * @param mixed $restaurantId
     *
     * @return string
     */
    public static function notAvailableWithin(string $attribute, mixed $restaurantId): string
    {
        $display = static::forDisplay($attribute);

        return "The given $display is not available within your restaurant (id: $restaurantId).";
    }

    /**
     * Get the message for the not available for role.
     *
     * @param string $attribute
     * @param array $roles
     *
     * @return string
     */
    public static function notAvailableForRole(string $attribute, array $roles): string
    {
        $roles = array_map(
            fn($role) => $role instanceof UserRole ? $role->value : $role,
            $roles
        );

        $display = static::forDisplay($attribute);

        return "The given $display is only available for roles: "
            . implode(', ', $roles) . '.';
    }

    /**
     * Get the message for the not available for preview only.
     *
     * @param string $attribute
     *
     * @return string
     */
    public static function notAvailableForPreviewOnly(string $attribute): string
    {
        $display = static::forDisplay($attribute);

        return "The given $display is only available for the super-admins.";
    }
}
