<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderKind;
use App\Enums\OrderState;
use App\Http\Requests\Crud\UpdateRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * Class UpdateOrderRequest.
 */
class UpdateOrderRequest extends UpdateRequest
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
            StoreOrderRequest::rulesForRelations(),
            [
                'kind' => [
                    'sometimes',
                    'nullable',
                    'string',
                    OrderKind::getValidationRule(),
                ],
                'state' => [
                    'sometimes',
                    'nullable',
                    'string',
                    OrderState::getValidationRule(),
                ],
                'slug' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:1',
                    'max:10',
                    Rule::unique('orders', 'slug')
                ],
                'recipient' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:1',
                    'max:100',
                ],
                'address' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'delivery_date' => [
                    'sometimes',
                    'nullable',
                    'date',
                ],
                'delivery_time' => [
                    'sometimes',
                    'nullable',
                    'date_format:H:i',
                ],
            ]
        );
    }

    /**
     * Ensures that product order fields are unique.
     *
     * @param Validator $validator
     *
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $orderId = $this->id();
            $products = collect($this->get('products'));

            $unique = $products->unique(function (array $field) use ($orderId) {
                $combination = Arr::only(
                    $field,
                    [
                        'batch',
                        'product_id',
                        'variant_id'
                    ]
                );

                return implode('-', $combination) . '-' . $orderId;
            });

            if ($unique->count() < $products->count()) {
                $validator->errors()
                    ->add(
                        'products',
                        'The order_id, batch, product_id and variant_id'
                        . ' combination must be unique within products.'
                    );
            }
        });
    }

    /**
     * @OA\Schema(
     *   schema="UpdateOrderRequest",
     *   description="Update order request",
     *   @OA\Property(property="slug", type="string", nullable=true, example="UD21P"),
     *   @OA\Property(property="kind", type="string", nullable=true, example="delivery",
     *     enum={"delivery", "banquet"}),
     *   @OA\Property(property="state", type="string", nullable=true, example="new",
     *     enum={"new", "confirmed", "postponed", "cancelled", "completed"}),
     *   @OA\Property(property="recipient", type="string", nullable=true, example="Andrii"),
     *   @OA\Property(property="phone", type="string", nullable=true, example="+380501234567"),
     *   @OA\Property(property="address", type="string", nullable=true,
     *     example="Street st. 5"),
     *   @OA\Property(property="delivery_time", type="string", format="time",
     *     nullable=true, example="12:00"),
     *   @OA\Property(property="delivery_date", type="string", format="date",
     *     nullable=true, example="2022-01-12"),
     *   @OA\Property(property="spaces", type="array",
     *     @OA\Items(ref ="#/components/schemas/StoreOrderRequestSpaceField")),
     *   @OA\Property(property="tickets", type="array",
     *     @OA\Items(ref ="#/components/schemas/StoreOrderRequestTicketField")),
     *   @OA\Property(property="services", type="array",
     *     @OA\Items(ref ="#/components/schemas/StoreOrderRequestServiceField")),
     *   @OA\Property(property="products", type="array",
     *     @OA\Items(ref ="#/components/schemas/StoreOrderRequestProductField")),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingComment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingDiscount")),
     *  )
     */
}
