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
use OpenApi\Annotations as OA;

/**
 * Class LoginController.
 */
class LoginController extends Controller
{
    /**
     * Login user.
     *
     * @OA\Post  (
     *   path="/api/login",
     *   summary="Login user.",
     *   operationId="login",
     *   tags={"auth"},
     *
     *   @OA\RequestBody(
     *     required=true,
     *     description="Login user request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/LoginRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User has been successfully logged in.",
     *     @OA\JsonContent(ref ="#/components/schemas/LoginResponse")
     *  ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation failed.",
     *     @OA\JsonContent(ref ="#/components/schemas/ValidationErrorsResponse")
     *   )
     * )
     *
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
        /** @phpstan-ignore-next-line */
        if (!Auth::guard('web')->attempt($credentials)) {
            return null;
        }
        /** @var User|null $user */
        $user = Auth::guard('web')->user();
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

    /**
     * @OA\Schema(
     *   schema="LoginResponse",
     *   description="Login response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/UserAndTokenResponse"),
     *   @OA\Property(property="message", type="string", example="Success")
     * )
     */
}
