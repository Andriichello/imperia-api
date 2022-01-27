<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class ApiResponse.
 */
class ApiResponse extends JsonResponse
{
    /**
     * ApiResponse constructor.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @param  bool  $json
     *
     * @return void
     */
    public function __construct(array $data = [], int $status = 200, array $headers = [], int $options = 0, bool $json = false)
    {
        parent::__construct($data, $status, $headers, $options, $json);
    }

    /**
     * Make instance of ApiResponse.
     *
     * @param array $data
     * @param int $status
     * @param string|null $message
     *
     * @return static
     */
    public static function make(array $data = [], int $status = 200, ?string $message = 'Success'): static
    {
        if ($message) {
            $data['message'] = $message;
        }
        return new ApiResponse($data, $status);
    }
}
