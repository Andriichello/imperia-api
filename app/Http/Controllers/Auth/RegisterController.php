<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;

/**
 * Class RegisterController.
 */
class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * RegisterController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

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
     *
     * @return JsonResponse
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = $this->userRepository->register($request->validated());

        $data = ['user' => new UserResource($user)];
        return ApiResponse::make(['data' => $data], 201, 'Registered');
    }

    /**
     * @OA\Schema(
     *   schema="RegisterResponse",
     *   description="Register user response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/User"),
     *   @OA\Property(property="message", type="string", example="Registered")
     * )
     */
}
