<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

/**
 * Class BaseRequest.
 */
class BaseRequest extends FormRequest
{
    /**
     * Message, which should be displayed on failed authorization attempt.
     *
     * @var string
     */
    protected string $message = 'You are not authorized to perform this request';

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws AuthorizationException
     */
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException($this->message);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get form request fields' default values.
     *
     * @return array
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * Create a new request instance from the given Laravel request.
     *
     * @param Request $from
     * @param Request|null $to
     *
     * @return static
     */
    public static function createFrom(Request $from, $to = null): static
    {
        $obj = parent::createFrom($from, $to);
        $obj->setContainer(app());
        $obj->getValidatorInstance();
        $obj->setRedirector(app(Redirector::class));

        return $obj;
    }

    /**
     * Get the validator instance for the request.
     *
     * @return Validator
     */
    protected function getValidatorInstance(): Validator
    {
        $defaults = $this->defaults();

        if (!empty($defaults)) {
            $this->mergeIfMissing($defaults);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get the user making the request.
     *
     * @param mixed $guard
     *
     * @return User|null
     */
    public function user(mixed $guard = null): ?User
    {
        return parent::user($guard);
    }

    /**
     * Get the id of user making the request.
     *
     * @param mixed $guard
     *
     * @return ?int
     */
    public function userId(mixed $guard = null): ?int
    {
        $user = parent::user($guard);
        return $user ? $user->id : null;
    }

    /**
     * Determine if user, who makes the request is an admin.
     *
     * @return bool
     */
    public function isByAdmin(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Determine if user, who makes the request is a manager.
     *
     * @return bool
     */
    public function isByManager(): bool
    {
        return $this->user()->isManager();
    }

    /**
     * Determine if user, who makes the request is a staff member.
     *
     * @return bool
     */
    public function isByStaff(): bool
    {
        return $this->user()->isStaff();
    }

    /**
     * Determine if user, who makes the request is a customer.
     *
     * @return bool
     */
    public function isByCustomer(): bool
    {
        return $this->user()->isCustomer();
    }
}
