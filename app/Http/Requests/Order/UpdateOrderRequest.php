<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Banquet;
use App\Models\Orders\Order;

/**
 * Class UpdateOrderRequest.
 */
class UpdateOrderRequest extends UpdateRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var Banquet $banquet */
        $banquet = Order::query()
            ->findOrFail($this->id());
        return $banquet->canBeEdited();
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
                    'min:1',
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
     *   schema="UpdateOrderRequest",
     *   description="Update order request",
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
}
