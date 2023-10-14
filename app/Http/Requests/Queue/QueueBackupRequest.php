<?php

namespace App\Http\Requests\Queue;

use App\Http\Requests\BaseRequest;

/**
 * Class QueueBackupRequest.
 */
class QueueBackupRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                //
            ]
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->isByStaff();
    }
}
