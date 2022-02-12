<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Class ApiResponse.
 */
class ApiResponse extends JsonResponse
{
    /**
     * ApiResponse constructor.
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @param int $options
     *
     * @return void
     */
    public function __construct(
        array $data = [],
        int $status = 200,
        array $headers = [],
        int $options = 0,
    ) {
        parent::__construct($data, $status, $headers, $options, false);
    }

    /**
     * Make instance of ApiResponse.
     *
     * @param array $data
     * @param int $status
     * @param string|null $message
     *
     * @return ApiResponse
     */
    public static function make(
        array $data = [],
        int $status = 200,
        ?string $message = 'Success',
    ): ApiResponse {
        if ($message) {
            $data['message'] = $message;
        }
        return new ApiResponse($data, $status);
    }
}
