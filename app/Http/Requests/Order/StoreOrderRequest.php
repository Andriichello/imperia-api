<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderKind;
use App\Enums\OrderState;
use App\Http\Requests\Crud\StoreRequest;
use App\Models\Morphs\Comment;
use App\Models\Morphs\Discount;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * Class StoreOrderRequest.
 */
class StoreOrderRequest extends StoreRequest
{
    public function messages(): array
    {
        return [
            'banquet_id.unique' => 'Specified banquet already has an order attached to it.'
        ];
    }

    /**
     * Get order's relations validation rules.
     *
     * @return array
     */
    public static function rulesForRelations(): array
    {
        $rules = [
            'spaces' => [
                'sometimes',
                'array',
            ],
            'spaces.*.space_id' => [
                'required',
                'integer',
                'distinct',
                'exists:spaces,id',
            ],

            'tickets' => [
                'sometimes',
                'array',
            ],
            'tickets.*.ticket_id' => [
                'required',
                'integer',
                'distinct',
                'exists:tickets,id',
            ],
            'tickets.*.amount' => [
                'required',
                'integer',
                'min:1',
            ],

            'services' => [
                'sometimes',
                'array',
            ],
            'services.*.service_id' => [
                'required',
                'integer',
                'distinct',
                'exists:services,id',
            ],
            'services.*.amount' => [
                'required',
                'integer',
                'min:1',
            ],
            'services.*.duration' => [
                'required',
                'integer',
                'min:0',
            ],

            'products' => [
                'sometimes',
                'array',
            ],
            'products.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'products.*.variant_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:product_variants,id',
            ],
            'products.*.batch' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'products.*.amount' => [
                'required',
                'integer',
                'min:1',
            ],
            'products.*.serve_at' => [
                'sometimes',
                'nullable',
                'string',
            ],
        ];
        return array_merge(
            $rules,
            Comment::rulesForAttaching(),
            Comment::rulesForAttaching('spaces.*.'),
            Comment::rulesForAttaching('tickets.*.'),
            Comment::rulesForAttaching('products.*.'),
            Comment::rulesForAttaching('services.*.'),
            Discount::rulesForAttaching(),
            Discount::rulesForAttaching('spaces.*.'),
            Discount::rulesForAttaching('tickets.*.'),
            Discount::rulesForAttaching('products.*.'),
            Discount::rulesForAttaching('services.*.'),
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            static::rulesForRelations(),
            [
                'banquet_id' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'exists:banquets,id',
                    'unique:orders,banquet_id',
                ],
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
                ]
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
            $products = collect($this->get('products'));

            $unique = $products->unique(function (array $field) {
                $combination = Arr::only(
                    $field,
                    [
                        'batch',
                        'product_id',
                        'variant_id'
                    ]
                );

                return implode('-', $combination);
            });

            if ($unique->count() < $products->count()) {
                $validator->errors()
                    ->add(
                        'products',
                        'The batch, product_id and variant_id'
                        . ' combination must be unique within products.'
                    );
            }
        });
    }

    /**
     * @OA\Schema(
     *   schema="StoreOrderRequest",
     *   description="Store order request",
     *   @OA\Property(property="banquet_id", type="integer", example=1),
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

    /**
     * @OA\Schema(
     *   schema="StoreOrderRequestSpaceField",
     *   description="Store order request space field",
     *   required={"space_id"},
     *   @OA\Property(property="space_id", type="integer", example=1),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingComment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingDiscount")),
     *  ),
     * @OA\Schema(
     *   schema="StoreOrderRequestTicketField",
     *   description="Store order request ticket field",
     *   required={"ticket_id", "amount"},
     *   @OA\Property(property="ticket_id", type="integer", example=1),
     *   @OA\Property(property="amount", type="integer", example=5),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingComment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingDiscount")),
     *  ),
     * @OA\Schema(
     *   schema="StoreOrderRequestServiceField",
     *   description="Store order request service field",
     *   required={"service_id", "amount", "duration"},
     *   @OA\Property(property="service_id", type="integer", example=1),
     *   @OA\Property(property="amount", type="integer", example=1),
     *   @OA\Property(property="duration", type="integer", example=90,
     *     description="Duration of the service rental in minutes."),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingComment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingDiscount")),
     *  ),
     * @OA\Schema(
     *   schema="StoreOrderRequestProductField",
     *   description="Store order request product field",
     *   required={"product_id", "variant_id", "amount"},
     *   @OA\Property(property="product_id", type="integer", example=1),
     *   @OA\Property(property="variant_id", type="integer", example=1),
     *   @OA\Property(property="batch", type="string", nullable=true, example="iSXC"),
     *   @OA\Property(property="amount", type="integer", example=3),
     *   @OA\Property(property="serve_at", type="string", nullable=true, example="16:50",
     *     description="24-hours format time, HOURS:MINUTES"),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingComment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingDiscount")),
     *  )
     */
}
