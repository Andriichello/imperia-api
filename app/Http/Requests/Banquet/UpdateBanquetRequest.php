<?php

namespace App\Http\Requests\Banquet;

use App\Helpers\BanquetHelper;
use App\Http\Requests\Crud\UpdateRequest;
use App\Http\Requests\Traits\GuardsBanquet;
use App\Models\Banquet;
use App\Models\Morphs\Comment;
use App\Models\Morphs\Discount;
use Illuminate\Contracts\Validation\Validator;

/**
 * Class UpdateBanquetRequest.
 */
class UpdateBanquetRequest extends UpdateRequest
{
    use GuardsBanquet;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var Banquet $banquet */
        $banquet = Banquet::query()
            ->findOrFail($this->id());

        return $this->canEdit($this->user(), $banquet);
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
                'advance_amount' => [
                    'sometimes',
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

        $helper = new BanquetHelper();
        $states = array_merge(
            $helper->availableTransferStates($banquet),
            [$banquet->state],
        );

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
     *   @OA\Property(property="title", type="string", example="Banquet title."),
     *   @OA\Property(property="description", type="string", example="Banquet description..."),
     *   @OA\Property(property="state", type="string", example="draft",
     *     description="Banquet state available changes depends on current state.
    All states: `draft`, `new`, `processing`, `completed`, `cancelled`."),
     *   @OA\Property(property="creator_id", type="integer", example=1),
     *   @OA\Property(property="customer_id", type="integer", example=1),
     *   @OA\Property(property="advance_amount", type="float", example=535.25),
     *   @OA\Property(property="start_at", type="string", format="date-time", example="2022-01-12 10:00:00",
     *     description="Date and time of when banquet should start."),
     *   @OA\Property(property="end_at", type="string", format="date-time", example="2022-01-12 23:00:00",
     *     description="Date and time of when banquet should end. Must be after or equal to `start_at`."),
     *   @OA\Property(property="paid_at", type="string", format="date-time", nullable="true", example=null,
     *     description="Date and time of when banquet was fully paid for."),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingComment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingDiscount")),
     *  )
     */
}
