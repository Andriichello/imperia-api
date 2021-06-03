<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class DatetimeUpdateRequest extends DataFieldRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'id' => (new IdentifierRule(0))->make(['required']),
            'day' => (new AmountRule(1,31))->make(),
            'month' => (new AmountRule(1,12))->make(),
            'year' => (new AmountRule(1900, 2100))->make(),
            'hours' => (new AmountRule(0,23))->make(),
            'minutes' => (new AmountRule(0,59))->make(),
            'is_templatable' => ['boolean'],
        ];

        return $this->nestWithData($rules);
    }
}
