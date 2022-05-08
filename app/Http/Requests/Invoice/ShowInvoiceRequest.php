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
        // todo: enable customer to see invoices for his/her banquets
        return $this->isByManager() || $this->isByAdmin();
    }
}
