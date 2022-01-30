<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class LoginController.
 */
class LoginController extends Controller
{
    /**
     * Login user.
     *
     * @group User management
     * @param LoginRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = $this->findUser($request);
        $token = $user->createToken($request->userAgent());

        $data = [
            'token' => $token->plainTextToken,
            'user' => new UserResource($user),
        ];
        return ApiResponse::make(['data' => $data]);
    }

    /**
     * Find user.
     *
     * @param LoginRequest $request
     *
     * @return User
     * @throws AuthenticationException
     */
    public function findUser(LoginRequest $request): User
    {
        if ($request->isByCredentials()) {
            $user = $this->findUserByCredentials($request->credentials());
        }

        if ($request->isByRememberToken()) {
            $user = $this->findUserByRememberToken($request->rememberToken());
        }

        if (empty($user)) {
            throw new AuthenticationException('Invalid credentials');
        }

        return $user;
    }

    /**
     * Find user by credentials.
     *
     * @param array $credentials
     *
     * @return User|null
     */
    protected function findUserByCredentials(array $credentials): ?User
    {
        if (!Auth::attempt($credentials)) {
            return null;
        }
        /** @var User|null $user */
        $user = Auth::user();
        return $user;
    }

    /**
     * Find user by remember token.
     *
     * @param string $rememberToken
     *
     * @return User|null
     */
    protected function findUserByRememberToken(string $rememberToken): ?User
    {
        /** @var User|null $user */
        $user = User::query()
            ->where('remember_token', $rememberToken)
            ->first();

        return $user;
    }
}
