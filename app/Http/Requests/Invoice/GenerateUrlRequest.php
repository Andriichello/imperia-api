<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\Crud\ShowRequest;
use App\Http\Requests\CrudRequest;
use App\Models\Banquet;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Annotations as OA;

/**
 * Class GenerateUrlRequest.
 */
class GenerateUrlRequest extends CrudRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $inRule = str_contains(request()->fullUrl(), '/api/orders/')
            ? 'in:pdf,view' : 'in:pdfThroughBanquet,viewThroughBanquet';

        return array_merge(
            parent::rules(),
            [
                'id' => [
                    'required',
                    'string',
                    'regex:/^[0-9]+(,[0-9]+)*$/',
                ],
                'endpoint' => [
                    'required',
                    'string',
                    $inRule,
                ]
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
        $ids = $this->ids();
        $user = $this->user();

        if (count($ids) > 1 && !$user->isStaff()) {
            return false;
        }

        foreach ($this->orders() as $order) {
            $banquet = $order->banquet;

            $hasPermissions = $banquet->creator_id === $user->id
                || in_array($banquet->customer_id, $user->customer_ids);

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
            explode(',', $this->get('id'))
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
     *   schema="OrderInvoiceUrlRequest",
     *   description="Generate url for accessing order's invoice request",
     *   @OA\Property(property="endpoint", type="string", example="pdf",
     *     enum={"pdf", "view"}),
     *  ),
     * @OA\Schema(
     *   schema="BanquetInvoiceUrlRequest",
     *   description="Generate url for accessing banquet's invoice request",
     *   @OA\Property(property="endpoint", type="string", example="pdfThroughBanquet",
     *     enum={"pdfThroughBanquet", "viewThroughBanquet"}),
     *  )
     */
}
