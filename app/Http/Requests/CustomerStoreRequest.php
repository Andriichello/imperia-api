<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class CustomerStoreRequest extends DataFieldRequest
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
            'name' => (new TextRule(2, 50))->make(['required']),
            'surname' => (new TextRule(2, 50))->make(['required']),
            'phone' => (new TextRule(2, 100, TextRule::PHONE_REGEX))->make(['required', 'unique:customers']),
            'email' => (new TextRule(2, 100))->make(['email']),
            'birthdate' => (new DateRule())->make(),
        ];

        return $this->nestWithData($rules);
    }
}
