<?php

namespace App\Http\Requests\Invoice;

use App\Models\Orders\Order;
use OpenApi\Annotations as OA;

/**
 * Class ShowMultipleInvoiceRequest.
 */
class ShowMultipleInvoiceRequest extends ShowInvoiceRequest
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
            [
                'ids' => [
                    'required',
                    'string',
                    'regex:/^[0-9]+(,[0-9]+)*$/'
                ],
            ]
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user->isStaff()) {
            return false;
        }

        foreach ($this->orders() as $order) {
            $banquet = $order->banquet;

            $hasPermissions = $banquet->creator_id === $user->id
                || $banquet->customer_id === $user->customer_id;

            if (!$hasPermissions) {
                return false;
            }

            if ($user->restaurant_id && $user->restaurant_id !== $banquet->restaurant_id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get ids of orders or banquets, which should be on the invoice.
     *
     * @return array|null
     */
    public function ids(): ?array
    {
        $ids = array_map(
            fn($id) => (int) $id,
            explode(',', $this->get('ids'))
        );

        return array_unique($ids);
    }

    /**
     * True if ids are for the orders.
     *
     * @return bool
     */
    public function isForOrders(): bool
    {
        return str_contains(request()->route()->getName(), 'api.orders');
    }

    /**
     * True if ids are for the banquets.
     *
     * @return bool
     */
    public function isForBanquets(): bool
    {
        return str_contains(request()->route()->getName(), 'api.banquets');
    }

    /**
     * Get orders, which were specified in the ids.
     *
     * @return Order[]
     */
    public function orders(): array
    {
        $column = $this->isForBanquets()
            ? 'banquet_id' : 'id';

        $orders = Order::query()
            ->whereIn($column, $this->ids())
            ->get();

        return $orders->all();
    }

    /**
     * @OA\Schema(
     *   schema="ShowMultipleInvoiceRequest",
     *   description="Show invoice for multiple orders request",
     *   @OA\Property(property="ids", type="string", example="1,2,3"),
     *   @OA\Property(property="tags", type="string", nullable=true,
     *     example="1,2"),
     *   @OA\Property(property="menus", type="string", nullable=true,
     *     example="1,2,3"),
     *   @OA\Property(property="sections", type="string", nullable=true,
     *     example="info,comments,tickets",
     *     description="Coma-separated list of invoice sections.
     *     Available sections: `info`, `comments`, `tickets`, `menus`,
     *     `spaces`, `services`"
     *   ),
     *  )
     */
}
