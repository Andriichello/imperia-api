<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

/**
 * Class RegisterController.
 */
class RegisterController extends Controller
{
    /**
     * Register user.
     *
     * @OA\Post  (
     *   path="/api/register",
     *   summary="Register user.",
     *   operationId="register",
     *   tags={"auth"},
     *
     *   @OA\RequestBody(
     *     required=true,
     *     description="Register user request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/RegisterRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="User has been successfully registered.",
     *     @OA\JsonContent(ref ="#/components/schemas/RegisterResponse")
     *  ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation failed.",
     *     @OA\JsonContent(ref ="#/components/schemas/ValidationErrorsResponse")
     *   )
     * )
     *
     * @param RegisterRequest $request
     * @param UserRepository $repository
     *
     * @return JsonResponse
     */
    public function __invoke(RegisterRequest $request, UserRepository $repository): JsonResponse
    {
        $user = $repository->create($request->validated());
        $token = $user->createToken($request->userAgent());

        $data = [
            'token' => $token->plainTextToken,
            'user' => new UserResource($user),
        ];
        return ApiResponse::make(['data' => $data], 201, 'Registered');
    }

    /**
     * @OA\Schema(
     *   schema="RegisterResponse",
     *   description="Register user response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/UserAndTokenResponse"),
     *   @OA\Property(property="message", type="string", example="Success")
     * )
     */
}
