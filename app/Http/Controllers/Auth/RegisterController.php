<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

/**
 * Class RegisterController.
 */
class RegisterController extends Controller
{
    /**
     * Register user.
     *
     * @group User management
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
}
