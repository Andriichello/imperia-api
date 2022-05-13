<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\Crud\DestroyRequest;

/**
 * Class DestroyNotificationRequest.
 */
class DestroyNotificationRequest extends DestroyRequest
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
