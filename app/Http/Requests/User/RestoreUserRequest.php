<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Http\Requests\Crud\RestoreRequest;

/**
 * Class RestoreUserRequest.
 */
class RestoreUserRequest extends RestoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::Admin);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                //
            ]
        );
    }
}
