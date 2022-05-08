<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Http\Requests\Crud\DestroyRequest;
use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Class DestroyUserRequest.
 */
class DestroyUserRequest extends DestroyRequest
{
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

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();
        /** @var User $target */
        $target = (new UserRepository())->find($this->id());

        if ($user->id === $target->id) {
            return true;
        }

        return $user->isAdmin() && !$target->isAdmin();
    }
}
