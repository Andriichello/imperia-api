<?php

namespace App\Http\Requests\Banquet;

use App\Enums\BanquetState;
use App\Http\Requests\Crud\StoreRequest;

/**
 * Class StoreBanquetRequest.
 */
class StoreBanquetRequest extends StoreRequest
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
                'title' => [
                    'required',
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
                    'required',
                    'string',
                    'in:' . BanquetState::Draft . ',' . BanquetState::New,
                ],
                'start_at' => [
                    'required',
                    'date',
                ],
                'end_at' => [
                    'required',
                    'date',
                    'after_or_equal:start_at',
                ],
                'paid_at' => [
                    'nullable',
                    'date',
                ],
                'creator_id' => [
                    'required',
                    'integer',
                    'exists:users,id',
                ],
                'customer_id' => [
                    'required',
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

    /**
     * @OA\Schema(
     *   schema="StoreBanquetRequest",
     *   description="Store banquet request",
     *   required = {"title", "description", "state", "start_at", "end_at", "creator_id", "customer_id"},
     *   @OA\Property(property="title", type="string", example="Banquet title."),
     *   @OA\Property(property="description", type="string", example="Banquet description..."),
     *   @OA\Property(property="state", type="string", example="draft", enum={"draft", "new"}),
     *   @OA\Property(property="creator_id", type="integer", example=1),
     *   @OA\Property(property="customer_id", type="integer", example=1),
     *   @OA\Property(property="advance_amount", type="float", example=535.25),
     *   @OA\Property(property="start_at", type="string", format="date-time", example="2022-01-12 10:00:00",
     *     description="Date and time of when banquet should start."),
     *   @OA\Property(property="end_at", type="string", format="date-time", example="2022-01-12 23:00:00",
     *     description="Date and time of when banquet should end. Must be after or equal to `start_at`."),
     *   @OA\Property(property="paid_at", type="string", format="date-time", nullable="true", example=null,
     *     description="Date and time of when banquet was fully paid for."),
     *  )
     */
}
