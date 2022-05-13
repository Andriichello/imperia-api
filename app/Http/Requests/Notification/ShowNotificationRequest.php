<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\Crud\ShowRequest;
use App\Models\Notification;

/**
 * Class ShowNotificationRequest.
 */
class ShowNotificationRequest extends ShowRequest
{
    /**
     * @var Notification
     */
    protected Notification $notification;

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
