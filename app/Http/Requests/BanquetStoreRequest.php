<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class BanquetStoreRequest extends DataFieldRequest
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
            'description' => (new TextRule(2, 100))->make(['nullable']),
            'advance_amount' => (new AmountRule(0))->make(['required']),
            'beg_datetime' => (new DateTimeRule())->make(['required']),
            'end_datetime' => (new DateTimeRule())->make(['required']),
            'state_id' => (new IdentifierRule(0))->make(['required']),
            'creator_id' => (new IdentifierRule(0))->make(['required']),
            'customer_id' => (new IdentifierRule(0))->make(['required']),
            'comments' => ['nullable', 'array'],
            'comments.*.text' => (new TextRule(1, 100))->make(),
            'comments.*.target_id' => (new IdentifierRule(0))->make(['required']),
            'comments.*.target_type' => (new TextRule(2, 100))->make(['required']),
        ];

        foreach (Banquet::getOrderColumnNames() as $orderType => $orderName) {
            $rules[$orderName] = [];
        }

        return $this->nestWithData($rules);
    }
}
