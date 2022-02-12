<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Class LoginRequest.
 */
class LoginRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required_without:remember_token',
                'email',
                'exists:users,email',
            ],
            'password' => [
                'required_without:remember_token',
                'string',
            ],
            'remember_token' => [
                'required_without:email,password',
                'string',
            ],
        ];
    }

    /**
     * Get credentials.
     *
     * @return array|null
     */
    public function credentials(): ?array
    {
        if (!$this->isByCredentials()) {
            return null;
        }
        return $this->only(['email', 'password']);
    }

    /**
     * Get remember token.
     *
     * @return string|null
     */
    public function rememberToken(): ?string
    {
        return $this->get('remember_token');
    }

    /**
     * Determine if login should be performed by credentials.
     *
     * @return bool
     */
    public function isByCredentials(): bool
    {
        return $this->has(['email', 'password']);
    }

    /**
     * Determine if login should be performed by remember token.
     *
     * @return bool
     */
    public function isByRememberToken(): bool
    {
        return $this->has('remember_token');
    }

    /**
     * @OA\Schema(
     *   schema="LoginRequest",
     *   description="Supports login by credantials (`email` and `password`) and
    by remember token (`remember_token`).",
     *   @OA\Property(property="email", type="string", example="john.doe@email.com",
     *     description="By credentials. Required with `password`"),
     *   @OA\Property(property="password", type="string", example="password",
     *     description="By credentials. Required with `email`"),
     *   @OA\Property(property="remember_token", type="string",
     *     description="By remember token"))
     * )
     */
}
