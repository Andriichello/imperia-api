<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\Crud\StoreRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'banquet_id' => [
                    'required',
                    'integer',
                    'unique:orders,banquet_id',
                ],

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
                'spaces.*.start_at' => [
                    'sometimes',
                    'date',
                    'after_or_equal:yesterday',
                ],
                'spaces.*.end_at' => [
                    'sometimes',
                    'date',
                    'after:start_at',
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
                    'distinct',
                    'exists:products,id',
                ],
                'products.*.amount' => [
                    'required',
                    'integer',
                    'min:1',
                ],
            ]
        );
    }

    /**
     * @OA\Schema(
     *   schema="StoreOrderRequest",
     *   description="Store order request",
     *   required={"banquet_id"},
     *   @OA\Property(property="banquet_id", type="integer", example=1),
     *   @OA\Property(property="spaces", type="array",
     *     @OA\Items(ref ="#/components/schemas/StoreOrderRequestSpaceField")),
     *   @OA\Property(property="tickets", type="array",
     *     @OA\Items(ref ="#/components/schemas/StoreOrderRequestTicketField")),
     *   @OA\Property(property="services", type="array",
     *     @OA\Items(ref ="#/components/schemas/StoreOrderRequestServiceField")),
     *   @OA\Property(property="products", type="array",
     *     @OA\Items(ref ="#/components/schemas/StoreOrderRequestProductField")),
     *  )
     */

    /**
     * @OA\Schema(
     *   schema="StoreOrderRequestSpaceField",
     *   description="Store order request space field",
     *   required={"space_id"},
     *   @OA\Property(property="space_id", type="integer", example=1),
     *   @OA\Property(property="start_at", type="string", format="date-time",
     *     description="If not present then banquet start_at date will be used."),
     *   @OA\Property(property="end_at", type="string", format="date-time",
     *     description="If not present then banquet end_at date will be used."),
     *  ),
     * @OA\Schema(
     *   schema="StoreOrderRequestTicketField",
     *   description="Store order request ticket field",
     *   required={"ticket_id", "amount"},
     *   @OA\Property(property="ticket_id", type="integer", example=1),
     *   @OA\Property(property="amount", type="integer", example=5),
     *  ),
     * @OA\Schema(
     *   schema="StoreOrderRequestServiceField",
     *   description="Store order request service field",
     *   required={"service_id", "amount", "duration"},
     *   @OA\Property(property="service_id", type="integer", example=1),
     *   @OA\Property(property="amount", type="integer", example=1),
     *   @OA\Property(property="duration", type="integer", example=90,
     *     description="Duration of the service rental in minutes."),
     *  ),
     * @OA\Schema(
     *   schema="StoreOrderRequestProductField",
     *   description="Store order request product field",
     *   required={"product_id", "amount"},
     *   @OA\Property(property="product_id", type="integer", example=1),
     *   @OA\Property(property="amount", type="integer", example=3),
     *  )
     */
}
