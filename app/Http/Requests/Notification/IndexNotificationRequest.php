<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\Crud\IndexRequest;

/**
 * Class IndexNotificationRequest.
 */
class IndexNotificationRequest extends IndexRequest
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
}
