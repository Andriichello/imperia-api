<?php

namespace App\Http\Requests\Banquet;

use App\Http\Requests\Crud\ShowRequest;
use App\Http\Requests\Traits\GuardsBanquet;
use App\Models\Banquet;

/**
 * Class ShowBanquetRequest.
 */
class ShowBanquetRequest extends ShowRequest
{
    use GuardsBanquet;

    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'creator',
                'customer',
                'comments',
                'discounts',
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
        /** @var Banquet $banquet */
        $banquet = Banquet::query()
            ->findOrFail($this->id());

        return $this->canAccess($this->user(), $banquet);
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
}
