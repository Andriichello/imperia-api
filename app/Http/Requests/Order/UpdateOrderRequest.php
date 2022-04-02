<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Orders\Order;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Class UpdateOrderRequest.
 */
class UpdateOrderRequest extends UpdateRequest
{
    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws AuthorizationException
     */
    protected function failedAuthorization()
    {
        $message = 'Order can\'t be updated, because banquet'
            . ' to which it belongs is in non-editable state.';

        throw new AuthorizationException($message);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var Order $order */
        $order = Order::query()
            ->findOrFail($this->id());
        return $order->canBeEdited();
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
            StoreOrderRequest::rulesForRelations(),
            [
                //
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
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingComment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingDiscount")),
     *  )
     */
}
