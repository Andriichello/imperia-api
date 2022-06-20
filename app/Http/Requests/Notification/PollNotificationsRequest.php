<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\Crud\IndexRequest;

/**
 * Class PollNotificationsRequest.
 */
class PollNotificationsRequest extends IndexRequest
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
                'seen' => [
                    'sometimes',
                ],
                'system' => [
                    'sometimes',
                ],
                'sender_id' => [
                    'sometimes',
                    'integer',
                    'not_in:' . $this->userId(),
                    'exists:users,id',
                ]
            ]
        );
    }

    protected function prepareForValidation(): void
    {
        $seen = $this->boolean('seen');
        $this->merge(compact('seen'));

        if ($this->get('system') !== null) {
            $system = $this->boolean('system');
            $this->merge(compact('system'));
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'sender_id.not_in' => 'The :attribute can not be the id of the user,'
                . ' who makes the request.'
        ];
    }
}
