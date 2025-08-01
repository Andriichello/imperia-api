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
     * Ability, which should be checked for the request.
     *
     * @var string|null
     */
    protected ?string $ability = 'showInvoice';

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
                'tags' => [
                    'sometimes',
                    'nullable',
                    'string',
                ],
                'menus' => [
                    'sometimes',
                    'nullable',
                    'string',
                ],
                'sections' => [
                    'sometimes',
                    'nullable',
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
     * Get tags, which should be on the invoice.
     *
     * @return array|null
     */
    public function tags(): ?array
    {
        $tags = $this->get('tags');

        if (is_string($tags)) {
            $result = [];

            foreach (explode(',', $tags) as $key => $menu) {
                $result[$key] = (int) $menu;
            }

            return $result;
        }

        return $tags;
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
