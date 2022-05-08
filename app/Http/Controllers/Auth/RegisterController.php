<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\CustomerRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Class RegisterController.
 */
class RegisterController extends Controller
{
    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * @var CustomerRepository
     */
    protected CustomerRepository $customerRepository;

    /**
     * RegisterController contstructor.
     *
     * @param UserRepository $userRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        UserRepository $userRepository,
        CustomerRepository $customerRepository
    ) {
        $this->userRepository = $userRepository;
        $this->customerRepository = $customerRepository;
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
        $user = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $role = data_get($data, 'role', UserRole::Customer);

            $user = $this->userRepository->create($data, $role);

            if ($role === UserRole::Customer) {
                $customer = $this->customerRepository->create($data);
                $customer->user_id = $user->id;
                $customer->save();
            }

            return $user;
        });

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
