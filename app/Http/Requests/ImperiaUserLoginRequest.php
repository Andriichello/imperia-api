<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;
use Illuminate\Foundation\Http\FormRequest;

class ImperiaUserLoginRequest extends FormRequest
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
        return [
            'name' => (new TextRule(2, 50))->make(['required_without:api_token']),
            'password' => (new TextRule(8, 50))->make(['required_without:api_token']),
            'api_token' => (new TextRule(0, null, null))->make(['required_without_all:name,password']),
        ];
    }
}
