<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\Crud\ShowRequest;
use App\Models\Banquet;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Annotations as OA;

/**
 * Class GenerateUrlRequest.
 */
class GenerateUrlRequest extends ShowRequest
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
        if ($this->isByStaff()) {
            return true;
        }

        $name = request()->route()->getName();
        if (str_contains($name, 'api.banquets')) {
            /** @var Banquet $banquet */
            $banquet = $this->targetOrFail(Banquet::class);
        }

        if (str_contains($name, 'api.orders')) {
            /** @var Order $order */
            $order = $this->targetOrFail(Order::class);
            $banquet = $order->banquet;
        }

        if (empty($banquet)) {
            throw new ModelNotFoundException('Failed to resolve banquet for given request.');
        }

        $user = $this->user();
        return $banquet->creator_id === $user->id
            || $banquet->customer_id === $user->customer_id;
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
