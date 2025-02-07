<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderKind;
use App\Enums\OrderState;
use App\Http\Requests\Crud\UpdateRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Arr;
use OpenApi\Annotations as OA;

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
     *    @OA\Property(property="kind", type="string", nullable=true, example="delivery",
     *     enum={"delivery", "banquet"}),
     *   @OA\Property(property="state", type="string", nullable=true, example="new",
     *     enum={"new", "confirmed", "postponed", "cancelled", "completed"}),
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
