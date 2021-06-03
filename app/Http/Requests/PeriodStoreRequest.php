<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class PeriodStoreRequest extends DataFieldRequest
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
            'beg_datetime_id' => (new IdentifierRule(0))->make([
                "required_if:{$this->dataFieldPrefix()}is_templatable,false",
                "required_without:{$this->dataFieldPrefix()}end_datetime_id"
            ]),
            'end_datetime_id' => (new IdentifierRule(0))->make([
                "required_if:{$this->dataFieldPrefix()}is_templatable,false",
                "required_without:{$this->dataFieldPrefix()}beg_datetime_id"]),
            'weekdays' => (new TextRule(0, 13, TextRule::PERIOD_WEEKDAYS_REGEX))->make(),
            'is_templatable' => ['required', 'boolean'],
        ];

        return $this->nestWithData($rules);
    }
}
