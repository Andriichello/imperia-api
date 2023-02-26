<?php

namespace App\Http\Requests\User;

/**
 * Class MeUserRequest.
 */
class MeUserRequest extends ShowUserRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'customer',
            ]
        );
    }

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
     * Get|set target id.
     *
     * @param mixed $id
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function id(mixed $id = false): mixed
    {
        if ($id !== false) {
            return $this->id = $id;
        }

        if (isset($this->id)) {
            return $this->id;
        }

        /** @phpstan-ignore-next-line  */
        return $this->id = $this->user()->id;
    }
}
