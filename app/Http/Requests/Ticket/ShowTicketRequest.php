<?php

namespace App\Http\Requests\Ticket;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowTicketRequest.
 */
class ShowTicketRequest extends ShowRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'categories',
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
}
