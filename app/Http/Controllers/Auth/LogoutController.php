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
     * @@authenticated
     * @group User management
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
}
