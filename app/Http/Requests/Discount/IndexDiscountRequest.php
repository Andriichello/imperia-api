<?php

namespace App\Http\Requests\Discount;

use App\Http\Requests\Crud\IndexRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Class IndexDiscountRequest.
 */
class IndexDiscountRequest extends IndexRequest
{
    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('target'),
            ]
        );
    }

    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                //
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
            [
                //
            ]
        );
    }

    /**
     * @OA\Schema(
     *   schema="AttachingDiscount",
     *   description="Attaching discount",
     *   required={"discount_id"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="discount_id", type="integer", example=1),
     *   @OA\Property(property="discountable_id", type="integer", example=1),
     *   @OA\Property(property="discountable_type", type="string", example="products"),
     *  ),
     */
}
