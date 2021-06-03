<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class CommentUpdateRequest extends DataFieldRequest
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
            'text' => (new TextRule(2, 50))->make(['required']),
            'target_id' => (new IdentifierRule(0))->make(['required']),
            'target_type' => (new TextRule(2, 35))->make(['required']),
            'container_id' => (new IdentifierRule(0))->make(['required']),
            'container_type' => (new TextRule(2, 35))->make(['required']),
        ];

        return $this->nestWithData($rules);
    }
}
