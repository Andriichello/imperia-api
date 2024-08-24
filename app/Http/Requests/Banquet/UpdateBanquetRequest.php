<?php

namespace App\Http\Requests\Banquet;

use App\Enums\BanquetState;
use App\Enums\PaymentMethod;
use App\Helpers\BanquetHelper;
use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Banquet;
use App\Models\Morphs\Comment;
use App\Models\Morphs\Discount;
use Illuminate\Contracts\Validation\Validator;
use OpenApi\Annotations as OA;

/**
 * Class UpdateBanquetRequest.
 */
class UpdateBanquetRequest extends UpdateRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'order',
                'creator',
                'customer',
                'comments',
                'discounts',
            ]
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
            Comment::rulesForAttaching(),
            Discount::rulesForAttaching(),
            [
                'title' => [
                    'nullable',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'description' => [
                    'nullable',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'state' => [
                    'string',
                ],
                'start_at' => [
                    'date',
                ],
                'end_at' => [
                    'date',
                    'after_or_equal:start_at',
                ],
                'paid_at' => [
                    'nullable',
                    'date',
                ],
                'creator_id' => [
                    'integer',
                    'exists:users,id',
                ],
                'customer_id' => [
                    'integer',
                    'exists:customers,id',
                ],
                'restaurant_id' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'exists:restaurants,id',
                ],
                'advance_amount' => [
                    'sometimes',
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'advance_amount_payment_method' => [
                    'sometimes',
                    'nullable',
                    'string',
                    PaymentMethod::getValidationRule(),
                ],
                'actual_total' => [
                    'sometimes',
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'is_birthday_club' => [
                    'sometimes',
                    'nullable',
                    'boolean',
                ],
                'with_photographer' => [
                    'sometimes',
                    'nullable',
                    'boolean',
                ],
                'adults_amount' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'min:0',
                ],
                'adult_ticket_price' => [
                    'sometimes',
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'children_amount' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'min:0',
                ],
                'child_ticket_price' => [
                    'sometimes',
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'children_amounts' => [
                    'sometimes',
                    'nullable',
                    'array',
                ],
                'children_amounts.*' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'min:0',
                ],
                'child_ticket_prices' => [
                    'sometimes',
                    'nullable',
                    'array',
                ],
                'child_ticket_prices.*' => [
                    'sometimes',
                    'nullable',
                    'numeric',
                    'min:0',
                ],
            ]
        );
    }

    public function withValidator(Validator $validator)
    {
        /** @var Banquet $banquet */
        $banquet = Banquet::query()->findOrFail($this->id());
        $states = [BanquetState::New];

        if ($this->isByStaff()) {
            $helper = new BanquetHelper();
            $states = array_merge(
                $helper->availableTransferStates($banquet),
                [$banquet->state],
            );
        }

        $validator->sometimes(
            'state',
            [
                'in:' . implode(',', $states),
            ],
            function () {
                return true;
            }
        );
    }

    /**
     * @OA\Schema(
     *   schema="UpdateBanquetRequest",
     *   description="Update banquet request",
     *   @OA\Property(property="title", type="string", nullable=true, example="Banquet title.",
     *   description="If null, then title will be autogenerated as Banquet-ID."),
     *   @OA\Property(property="description", type="string", example="Banquet description..."),
     *   @OA\Property(property="state", type="string", example="draft",
     *     description="Banquet state available changes depends on current state.
           All states: `new`, `confirmed`, `postponed`, `cancelled`, `completed`."),
     *   @OA\Property(property="creator_id", type="integer", example=1),
     *   @OA\Property(property="customer_id", type="integer", example=1),
     *   @OA\Property(property="restaurant_id", type="integer", nullable=true, example=1),
     *   @OA\Property(property="advance_amount", type="float", example=535.25),
     *   @OA\Property(property="actual_total", type="float", nullable=true, example=692.25),
     *   @OA\Property(property="advance_amount_payment_method", type="string",
     *     nullable=true, example="card", enum={"card", "cash"}),
     *   @OA\Property(property="is_birthday_club", type="boolean", nullable=true, example="true"),
     *   @OA\Property(property="with_photographer", type="boolean", nullable=true, example="true"),
     *   @OA\Property(property="children_amount", type="integer", nullable=true, example=5),
     *   @OA\Property(property="child_ticket_price", type="float", nullable=true, example=25.50),
     *   @OA\Property(property="children_amounts", type="array", @OA\Items(type="integer", example=5)),
     *   @OA\Property(property="child_ticket_prices", type="array", @OA\Items(type="float", example=25.50)),
     *   @OA\Property(property="adults_amount", type="integer", nullable=true, example=2),
     *   @OA\Property(property="adult_ticket_price", type="float", nullable=true, example=35.50),
     *   @OA\Property(property="start_at", type="string", format="date-time", example="2022-01-12 10:00:00",
     *     description="Date and time of when banquet should start."),
     *   @OA\Property(property="end_at", type="string", format="date-time", example="2022-01-12 23:00:00",
     *     description="Date and time of when banquet should end. Must be after or equal to `start_at`."),
     *   @OA\Property(property="paid_at", type="string", format="date-time", nullable=true, example=null,
     *     description="Date and time of when banquet was fully paid for."),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingComment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingDiscount")),
     *  )
     */
}
