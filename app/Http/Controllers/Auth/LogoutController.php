<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Class LogoutController.
 */
class LogoutController extends Controller
{
    /**
     * Logout user.
     *
     * @OA\Post  (
     *   path="/api/logout",
     *   summary="Logout user.",
     *   operationId="logout",
     *   tags={"auth"},
     *
     *   @OA\Response(
     *     response=200,
     *     description="User has been successfully logged out.",
     *     @OA\JsonContent(ref ="#/components/schemas/LogoutResponse")
     *  ),
     * )
     *
     * @param BaseRequest $request
     *
     * @return JsonResponse
     */
    public function __invoke(BaseRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->tokens()->delete();

        return ApiResponse::make([], 200, 'Logged out');
    }

    /**
     * @OA\Schema(
     *   schema="LogoutResponse",
     *   description="Logout user response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Logged out")
     * )
     */
}
