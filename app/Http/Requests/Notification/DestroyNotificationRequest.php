<?php

namespace App\Http\Requests\Notification;

use App\Enums\UserRole;
use App\Http\Requests\Crud\DestroyRequest;
use App\Models\Notification;
use App\Models\Orders\Order;
use App\Models\User;
use App\Repositories\UserRepository;

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

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if ($this->isByAdmin()) {
            return true;
        }

        /** @var Notification $notification */
        $notification = Notification::query()
            ->findOrFail($this->id());

        return $notification->sender_id === $this->userId();
    }
}
