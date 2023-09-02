<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\Crud\ShowRequest;
use App\Models\Banquet;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Annotations as OA;

/**
 * Class ShowInvoiceRequest.
 */
class ShowInvoiceRequest extends ShowRequest
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
                'menus' => [
                    'sometimes',
                    'string',
                ],
                'sections' => [
                    'sometimes',
                    'string',
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
     * Get menus, which should be on the invoice.
     *
     * @return array|null
     */
    public function menus(): ?array
    {
        $menus = $this->get('menus');

        if (is_string($menus)) {
            $result = [];

            foreach (explode(',', $menus) as $key => $menu) {
                $result[$key] = (int) $menu;
            }

            return $result;
        }

        return $menus;
    }

    /**
     * Get sections, which should be on the invoice.
     *
     * @return array|null
     */
    public function sections(): ?array
    {
        $sections = $this->get('sections');

        return is_string($sections)
            ? explode(',', $sections) : $sections;
    }

    /**
     * @OA\Schema(
     *   schema="ShowInvoiceRequest",
     *   description="Show invoice request",
     *   @OA\Property(property="menus", type="string", example="1,2,3"),
     *   @OA\Property(property="sections", type="string",
     *     example="info,comments,tickets",
     *     description="Coma-separated list of invoice sections.
     *     Available sections: `info`, `comments`, `tickets`, `menus`,
     *     `spaces`, `services`"
     *   ),
     *  )
     */
}
