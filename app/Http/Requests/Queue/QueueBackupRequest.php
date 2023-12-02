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
                'only' => [
                    'sometimes',
                    'nullable',
                    'array',
                ],
                'only.*' => [
                    'required',
                    'string',
                    'min:1',
                    'in:db,files'
                ],
            ]
        );
    }

    /**
     * If true, then database should be backed up.
     *
     * @return bool
     */
    public function isDb(): bool
    {
        $only = $this->get('only');

        if (empty($only)) {
            return false;
        }

        return in_array('db', $only);
    }

    /**
     * If true, then files should be backed up.
     *
     * @return bool
     */
    public function isFiles(): bool
    {
        $only = $this->get('only');

        if (empty($only)) {
            return false;
        }

        return in_array('files', $only);
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
