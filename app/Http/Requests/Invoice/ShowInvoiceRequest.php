<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowInvoiceRequest.
 */
class ShowInvoiceRequest extends ShowRequest
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
                'signature' => [
                    'required',
                    'string',
                    'min:32'
                ]
            ]
        );
    }
}
