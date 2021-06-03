<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class DatetimeStoreRequest extends DataFieldRequest
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
        $prefixed = [
            'hours' => $this->dataFieldPrefix() . 'hours',
            'minutes' => $this->dataFieldPrefix() . 'minutes',
            'day' => $this->dataFieldPrefix() . 'day',
            'month' => $this->dataFieldPrefix() . 'month',
            'year' => $this->dataFieldPrefix() . 'year',
        ];

        $rules = [
            'day' => (new AmountRule(1, 31))->make([
                "required_if:{$this->dataFieldPrefix()}is_templatable,false",
                'required_without_all:' . implode(',', [
                    $prefixed['month'],
                    $prefixed['year'],
                    $prefixed['hours'],
                    $prefixed['minutes'],
                ])
            ]),
            'month' => (new AmountRule(1, 12))->make([
                "required_if:{$this->dataFieldPrefix()}is_templatable,false",
                'required_without_all:' . implode(',', [
                    $prefixed['day'],
                    $prefixed['year'],
                    $prefixed['hours'],
                    $prefixed['minutes'],
                ])
            ]),
            'year' => (new AmountRule(1900, 2100))->make([
                "required_if:{$this->dataFieldPrefix()}is_templatable,false",
                'required_without_all:' . implode(',', [
                    $prefixed['day'],
                    $prefixed['month'],
                    $prefixed['hours'],
                    $prefixed['minutes'],
                ])
            ]),
            'hours' => (new AmountRule(0, 23))->make([
                "required_if:{$this->dataFieldPrefix()}is_templatable,false",
                'required_without_all:' . implode(',', [
                    $prefixed['day'],
                    $prefixed['month'],
                    $prefixed['year'],
                    $prefixed['minutes'],
                ])
            ]),
            'minutes' => (new AmountRule(0, 59))->make([
                "required_if:{$this->dataFieldPrefix()}is_templatable,false",
                'required_without_all:' . implode(',', [
                    $prefixed['day'],
                    $prefixed['month'],
                    $prefixed['year'],
                    $prefixed['hours'],
                ])
            ]),
            'is_templatable' => ['boolean', 'required'],
        ];

        return $this->nestWithData($rules);
    }
}
