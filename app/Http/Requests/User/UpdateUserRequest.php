<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Http\Requests\Crud\UpdateRequest;
use App\Models\User;
use App\Repositories\UserRepository;

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
            if (!$target->isCurrentPassword($this->get('current_password'))) {
                $this->validator->errors()
                    ->add('current_password', 'Invalid current password');

                return false;
            }
        }

        if ($user->id === $target->id) {
            return true;
        }

        return $user->isAdmin() && !$target->isAdmin();
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

    /**
     * @OA\Schema(
     *   schema="UpdateUserRequest",
     *   description="Update user request",
     *   @OA\Property(property="name", type="string", example="John"),
     *   @OA\Property(property="email", type="string", example="ben.forrester@email.com"),
     *   @OA\Property(property="password", type="string", example="new-pa$$w0rd",
     *     description="This is a new password. It must be different than the current one."),
     *   @OA\Property(property="current_password", type="string", example="pa$$w0rd",
     *     description="This is a current password. It is needed in order to perform
     authorization. Required with `password`."),
     * )
     */
}
