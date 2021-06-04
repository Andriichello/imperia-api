<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class OrderStoreRequest extends DataFieldRequest
{
    /**
     * Order type.
     *
     * @var string
     */
    protected string $type;

    /**
     * Get order type.
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Set order type.
     *
     * @param string $type
     * @return static
     */
    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

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
            'banquet_id' => (new IdentifierRule(0))->make(['required']),
            'discount_id' => (new IdentifierRule(0))->make(),
            'items' => ['nullable', 'array'],
            'items.*.id' => (new IdentifierRule(0))->make(['required']),
        ];

        switch ($this->type()) {
            case 'space':
                $rules['items.*.beg_datetime'] = (new DateTimeRule())->make(['required']);
                $rules['items.*.end_datetime'] = (new DateTimeRule())->make(['required']);
                break;
            case 'ticket':
                $rules['items.*.amount'] = (new AmountRule(0))->make(['required']);
                break;
            case 'service':
                $rules['items.*.amount'] = (new AmountRule(0))->make(['required']);
                $rules['items.*.duration'] = (new AmountRule(0))->make(['required']);
                break;
            case 'product':
                $rules['items.*.amount'] = (new AmountRule(0))->make(['required']);
                break;
        }

        return $this->nestWithData($rules);
    }
}
