<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class BanquetUpdateRequest extends DataFieldRequest
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
            'id' => (new IdentifierRule(0))->make(),
            'name' => (new TextRule(2, 50))->make(),
            'description' => (new TextRule(2, 100))->make(['nullable']),
            'advance_amount' => (new AmountRule(0))->make(),
            'beg_datetime' => (new DateTimeRule())->make(),
            'end_datetime' => (new DateTimeRule())->make(),
            'state_id' => (new IdentifierRule(0))->make(),
            'creator_id' => (new IdentifierRule(0))->make(),
            'customer_id' => (new IdentifierRule(0))->make(),
            'comments' => ['nullable', 'array'],
            'comments.*.id' => (new IdentifierRule(0))->make([
                'required_without:' . $this->dataFieldPrefix() . 'comments.*.text'
            ]),
            'comments.*.text' => (new TextRule(1, 100))->make([
                'required_without:' . $this->dataFieldPrefix() . 'comments.*.id'
            ]),
            'comments.*.target_id' => (new IdentifierRule(0))->make(['required']),
            'comments.*.target_type' => (new TextRule(2, 100))->make(['required']),
        ];

        foreach (Banquet::getOrderColumnNames() as $orderType => $orderName) {
            $rules[$orderName] = [];
        }

        return $this->nestWithData($rules);
    }
}
