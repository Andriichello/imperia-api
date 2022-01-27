<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Http\Requests\Crud\UpdateRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

/**
 * Class UpdateUserRequest.
 */
class UpdateUserRequest extends UpdateRequest
{
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

        if ($this->has('current_password')) {
            $credentials = [
                'email' => $target->email,
                'password' => $this->get('current_password'),
            ];

            if (!Auth::attempt($credentials)) {
                $this->validator->errors()
                    ->add('current_password', 'Invalid current password');

                return false;
            }
        }

        if ($user->id === $target->id) {
            return true;
        }

        return $user->hasRole(UserRole::Admin) && !$target->hasRole(UserRole::Admin);
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
                'name' => [
                    'string',
                    'min:2',
                    'max:100',
                ],
                'email' => [
                    'sometimes',
                    'email',
                ],
                'password' => [
                    'string',
                    'min:8',
                    'max:255',
                ],
                'current_password' => [
                    'required_with:password',
                    'string',
                    'different:password',
                ],
            ]
        );
    }
}
