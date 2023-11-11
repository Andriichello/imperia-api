<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\Status\CheckStatusRequest;
use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Class StatusController.
 */
class StatusController extends Controller
{
    /**
     * Check API status.
     *
     * @param CheckStatusRequest $request
     *
     * @return JsonResponse
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function check(CheckStatusRequest $request): JsonResponse
    {
        $passed = [];
        $failed = [];

        if ($request->get('db')) {
            try {
                DB::connection()->getPdo();
                $passed['database'] = DB::connection()->getDatabaseName();
            } catch (Exception $exception) {
                $failed['database'] = $exception->getMessage();
                $message = 'Failed connecting to the database.';
            }
        }

        $success = empty($failed);
        $payload = compact('success', 'passed', 'failed');

        return ApiResponse::make($payload, $success ? 200 : 500, $message ?? null);
    }

    /**
     * List API vars.
     *
     * @return JsonResponse
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function list(): JsonResponse
    {
        return ApiResponse::make(['env' => $_ENV]);
    }
}
